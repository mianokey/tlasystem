<?php

namespace App\Services;

use App\Models\FeesClassType;
use App\Models\FeesType;
use App\Models\StudentFeeInvoice;
use App\Repositories\ExtraFormField\ExtraFormFieldsInterface;
use App\Repositories\Student\StudentInterface;
use App\Repositories\User\UserInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\StudentFeeInvoiceItem;
use App\Models\Students;
use Illuminate\Support\Str;
use JsonException;
use Throwable;

class UserService
{
    private UserInterface $user;
    private StudentInterface $student;
    private ExtraFormFieldsInterface $extraFormFields;

    public function __construct(UserInterface $user, StudentInterface $student, ExtraFormFieldsInterface $extraFormFields)
    {
        $this->user = $user;
        $this->student = $student;
        $this->extraFormFields = $extraFormFields;
    }

    public function makeParentPassword($mobile)
    {
        return '12345678';
    }

    public function makeStudentPassword($dob)
    {
        return '12345678';
    }

    public function createOrUpdateParent($first_name, $last_name, $email, $mobile, $gender, $image = null)
    {
        $password = $this->makeParentPassword($mobile);

        $parent = array(
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'mobile'     => $mobile,
            'gender'     => $gender,
            'school_id'  => null
        );

        $user = $this->user->guardian()->where('email', $email)->first();

        if (!empty($image)) {
            $parent['image'] = UploadService::upload($image, 'guardian');
        }

        if (!empty($user)) {
            if (isset($parent['image'])) {
                if (Storage::disk('public')->exists($user->getRawOriginal('image'))) {
                    Storage::disk('public')->delete($user->getRawOriginal('image'));
                }
            }

            $user->update($parent);
        } else {
            $parent['password'] = Hash::make($password);
            $parent['email'] = $email;
            $user = $this->user->create($parent);
            $user->assignRole('Guardian');
        }

        return $user;
    }


    public function createStudentUser(
        string $first_name,
        string $last_name,
        string $admission_no,
        string|null $mobile,
        string $dob,
        string $gender,
        UploadedFile|null $image,
        int $classSectionID,
        string $admissionDate,
        $current_address = null,
        $permanent_address = null,
        int $sessionYearID,
        int $guardianID,
        array $extraFields = [],
        int $status,
        string $school_transport
    ) {

        $password = $this->makeStudentPassword($dob);

        $user = $this->user->create([
            'first_name'        => $first_name,
            'last_name'         => $last_name,
            'email'             => $admission_no,
            'mobile'            => $mobile,
            'dob'               => date('Y-m-d', strtotime($dob)),
            'gender'            => $gender,
            'password'          => Hash::make($password),
            'school_id'         => Auth::user()->school_id,
            'image'             => $image,
            'status'            => $status,
            'current_address'   => $current_address,
            'permanent_address' => $permanent_address,
            'deleted_at'        => $status == 1 ? null : '1970-01-01 01:00:00'
        ]);

        

        $user->assignRole('Student');

        $roll_number_db = $this->student->builder()
            ->select(DB::raw('max(roll_number)'))
            ->where('class_section_id', $classSectionID)
            ->first();

        $roll_number = ($roll_number_db['max(roll_number)'] ?? 0) + 1;

        $student = $this->student->create([
            'user_id'          => $user->id,
            'class_section_id' => $classSectionID,
            'admission_no'     => $admission_no,
            'roll_number'      => $roll_number,
            'admission_date'   => date('Y-m-d', strtotime($admissionDate)),
            'guardian_id'      => $guardianID,
            'session_year_id'  => $sessionYearID,
            'school_transport' => $school_transport ?? 'no'
        ]);

        $student->student_id = $student->id;

        $extraDetails = [];

        foreach ($extraFields as $fields) {
            $data = null;

            if (isset($fields['data'])) {
                $data = is_array($fields['data'])
                    ? json_encode($fields['data'], JSON_THROW_ON_ERROR)
                    : $fields['data'];
            }

            $extraDetails[] = [
                'student_id'    => $student->user_id,
                'form_field_id' => $fields['form_field_id'],
                'data'          => $data,
            ];
        }

        if (!empty($extraDetails)) {
            $this->extraFormFields->createBulk($extraDetails);
        }

        $guardian = $this->user->guardian()->where('id', $guardianID)->firstOrFail();
        $parentPassword = $this->makeParentPassword($guardian->mobile);

        $this->sendRegistrationEmail(
            $guardian->email,
            $guardian->full_name,
            $parentPassword,
            $user->full_name,
            $student->admission_no,
            $password
        );


       return [
            'user' => $user,
            'student' => $student
        ];

    }
    public function updateStudentUser(
        $userID,
        $first_name,
        $last_name,
        $mobile,
        $dob,
        $gender,
        $image,
        $sessionYearID,
        $extraFields = [],
        $guardianID = null,
        $current_address = null,
        $permanent_address = null,
        $reset_password = null,
        $school_transport
    ) {

        $studentUserData = [
            'first_name'        => $first_name,
            'last_name'         => $last_name,
            'mobile'            => $mobile,
            'dob'               => date('Y-m-d', strtotime($dob)),
            'current_address'   => $current_address,
            'permanent_address' => $permanent_address,
            'gender'            => $gender,
        ];

        if ($image) {
            $studentUserData['image'] = $image;
        }

        if (isset($reset_password)) {
            $studentUserData['password'] = Hash::make($this->makeStudentPassword($dob));
        }

        // update user
        $user = $this->user->update($userID, $studentUserData);

        // ✅ UPDATE STUDENT INCLUDING TRANSPORT
        $studentData = [
            'guardian_id'      => $guardianID,
            'session_year_id'  => $sessionYearID,
            'school_transport' => $school_transport ?? 'no'
        ];

        $student = $this->student->update($user->student->id, $studentData);

        $extraDetails = [];

        foreach ($extraFields as $fields) {
            $data = null;

            if (isset($fields['data'])) {
                $data = is_array($fields['data'])
                    ? json_encode($fields['data'], JSON_THROW_ON_ERROR)
                    : $fields['data'];
            }

            $extraDetails[] = [
                'id'            => $fields['id'],
                'student_id'    => $student->user_id,
                'form_field_id' => $fields['form_field_id'],
                'data'          => $data,
            ];
        }

        $this->extraFormFields->upsert($extraDetails, ['id'], ['data']);

        $user->assignRole('Student');

        DB::commit();

        return $user;
    }

    public function generateForStudent(Students $student): void
    {
        $classId = $student->class_section->class_id;

        $invoice = StudentFeeInvoice::create([
            'student_id' => $student->id,
            'session_year_id' => $student->session_year_id,
            'invoice_no' => $this->generateInvoiceNo(),
            'school_id' => $student->school_id,
            'total_amount' => 0,
            'paid_amount' => 0,
            'balance' => 0,
            'status' => 'unpaid',
        ]);

        $total = 0;

        // 1. COMPULSORY FEES
        $compulsoryFees = FeesClassType::where('class_id', $classId)
            ->where('optional', 0)
            ->get();

        foreach ($compulsoryFees as $fee) {
            StudentFeeInvoiceItem::create([
                'student_fee_invoice_id' => $invoice->id,
                'fees_id' => $fee->fees_id,
                'fees_type_id' => $fee->fees_type_id,
                'amount' => $fee->amount,
                
            ]);

            $total += $fee->amount;
        }

        // 2. OPTIONAL FEES (e.g transport)
        $this->addOptionalFees($student, $invoice, $total);

        // 3. UPDATE INVOICE TOTAL
        $invoice->update([
            'total_amount' => $total,
            'balance' => $total,
        ]);
    }

    private function addOptionalFees($student, $invoice, &$total): void
    {
        $feesType = FeesType::where('name', 'like', '%Transport%')->first();

        if ($student->school_transport && $student->school_transport !== 'no' && $feesType) {

            $feeClass = FeesClassType::where('fees_type_id', $feesType->id)
                ->where('class_id', $student->class_section->class_id)
                ->first();

            if ($feeClass) {
                StudentFeeInvoiceItem::create([
                    'student_fee_invoice_id' => $invoice->id,
                    'fees_id' => $feeClass->fees_id,
                    'fees_type_id' => $feesType->id,
                    'amount' => $feeClass->amount,
                ]);

                $total += $feeClass->amount;
            }
        }
    }

    public function generateInvoiceNo(): string
    {
        $date = now()->format('ymd');

        // 4-character hex = very compact + large space
        $random = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));

        return 'INV' . $date . $random;
    }



    public function sendRegistrationEmail($email, $name, $plainTextPassword, $childName, $childAdmissionNumber, $childPlainTextPassword)
    {
        try {
            $school_name = Auth::user()->school->name;

            $data = [
                'subject' => 'Welcome to ' . $school_name,
                'email'   => $email,
                'name'    => $name,
                'username' => $email,
                'password' => $plainTextPassword,
                'child_name' => $childName,
                'child_admission_number' => $childAdmissionNumber,
                'child_password' => $childPlainTextPassword,
            ];

            Mail::send('students.email', $data, function ($message) use ($data) {
                $message->to($data['email'])->subject($data['subject']);
            });
        } catch (\Throwable $th) {
        }
    }

    /*
    ==========================================================
    BACKUP CODE (PRESERVED EXACTLY AS REQUESTED)
    ==========================================================

    public function createOrUpdateStudentUser($first_name, $last_name, $admission_no, $mobile, $dob, $gender, $image, $classSectionID, $admissionDate, array $extraFields = [], $rollNumber = null, $guardianID = null) {
        $password = $this->makeStudentPassword($dob);
        $userExists = $this->user->builder()->where('email', $admission_no)->first();
        if (!empty($rollNumber)) {
            $rollNumber = $this->student->builder()->select(DB::raw('max(roll_number)'))->where('class_section_id', $classSectionID)->first();
            $rollNumber = $rollNumber['max(roll_number)'];
            ++$rollNumber;
        }
        $studentUserData = array(
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'email'      => $admission_no,
            'mobile'     => $mobile,
            'dob'        => date('Y-m-d', strtotime($dob)),
            'gender'     => $gender,
        );

        $studentData = array(
            'class_section_id' => $classSectionID,
            'admission_no'     => $admission_no,
            'roll_number'      => $rollNumber,
            'guardian_id'      => $guardianID
        );


        if (!$userExists) {
            $studentUserData = array_merge($studentUserData, [
                'password'  => Hash::make($password),
                'school_id' => Auth::user()->school_id,
                'image'     => $image
            ]);
            $user = $this->user->create($studentUserData);
            $user->assignRole('Student');

            $sessionYear = $this->sessionYear->default();
            $studentData = array_merge($studentData, [
                'user_id'         => $user->id,
                'admission_date'  => date('Y-m-d', strtotime($admissionDate)),
                'session_year_id' => $sessionYear->id
            ]);
            $student = $this->student->create($studentData);

        } else {
            if ($image) {
                $studentUserData['image'] = $image;
            }
            $user = $this->user->update($userExists->id, $studentUserData);
            $student = $this->student->update($user->student->id, $studentData);
        }

        $extraDetails = [];
        foreach ($extraFields as $fields) {
            if ($fields['input_type'] == 'file' && !isset($fields['data'])) {
                continue;
            }
            $data = null;
            if (isset($fields['data'])) {
                $data = is_array($fields['data'])
                    ? json_encode($fields['data'], JSON_THROW_ON_ERROR)
                    : $fields['data'];
            }
            $extraDetails[] = [
                'id'            => $fields['id'] ?? null,
                'student_id'    => $student->id,
                'form_field_id' => $fields['form_field_id'],
                'data'          => $data,
            ];
        }

        $this->extraFormFields->upsert($extraDetails, ['student_id', 'form_field_id'], ['data']);

        DB::commit();

        if (!$userExists) {
            $guardian = $this->user->findById($guardianID);
            $password = $this->makeParentPassword($first_name, $mobile);
            $this->sendRegistrationEmail($guardian->email, $guardian->full_name, $password, $user->full_name, $student->admission_no, $password);
        }

        return $user;
    }
    */
}
