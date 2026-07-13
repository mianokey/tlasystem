<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\FeesAdvance;
use App\Models\FeesClassType;
use App\Models\PaymentTransaction;
use App\Models\Semester;
use App\Models\StudentCreditTransaction;
use App\Models\StudentFeeInvoice;
use App\Models\StudentFeeInvoiceItem;
use App\Models\Students;
use App\Repositories\ClassSchool\ClassSchoolInterface;
use App\Repositories\CompulsoryFee\CompulsoryFeeInterface;
use App\Repositories\Fees\FeesInterface;
use App\Repositories\FeesClassType\FeesClassTypeInterface;
use App\Repositories\FeesInstallment\FeesInstallmentInterface;
use App\Repositories\FeesPaid\FeesPaidInterface;
use App\Repositories\FeesType\FeesTypeInterface;
use App\Repositories\Medium\MediumInterface;
use App\Repositories\OptionalFee\OptionalFeeInterface;
use App\Repositories\PaymentConfiguration\PaymentConfigurationInterface;
use App\Repositories\PaymentTransaction\PaymentTransactionInterface;
use App\Repositories\SchoolSetting\SchoolSettingInterface;
use App\Repositories\SessionYear\SessionYearInterface;
use App\Repositories\Student\StudentInterface;
use App\Repositories\SystemSetting\SystemSettingInterface;
use App\Repositories\User\UserInterface;
use App\Services\BootstrapTableService;
use App\Services\CachingService;
use App\Services\ResponseService;
use App\Services\UserService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class FeesController extends Controller
{
    private FeesInterface $fees;
    private SessionYearInterface $sessionYear;
    private FeesInstallmentInterface $feesInstallment;
    private SchoolSettingInterface $schoolSettings;
    private MediumInterface $medium;
    private FeesTypeInterface $feesType;
    private ClassSchoolInterface $classes;
    private FeesClassTypeInterface $feesClassType;
    private UserInterface $user;
    private FeesPaidInterface $feesPaid;
    private CompulsoryFeeInterface $compulsoryFee;
    private OptionalFeeInterface $optionalFee;
    private UserService $userService;
    private CachingService $cache;
    private PaymentConfigurationInterface $paymentConfigurations;
    private ClassSchoolInterface $class;
    private StudentInterface $student;
    private PaymentTransactionInterface $paymentTransaction;
    private SystemSettingInterface $systemSetting;

    public function __construct(FeesInterface $fees, SessionYearInterface $sessionYear, FeesInstallmentInterface $feesInstallment, SchoolSettingInterface $schoolSettings, MediumInterface $medium, FeesTypeInterface $feesType, ClassSchoolInterface $classes, FeesClassTypeInterface $feesClassType, UserInterface $user, FeesPaidInterface $feesPaid, CompulsoryFeeInterface $compulsoryFee, OptionalFeeInterface $optionalFee, UserService $userService, CachingService $cache, PaymentConfigurationInterface $paymentConfigurations, ClassSchoolInterface $classSchool, StudentInterface $student, PaymentTransactionInterface $paymentTransaction, SystemSettingInterface $systemSetting)
    {
        $this->fees = $fees;
        $this->sessionYear = $sessionYear;
        $this->feesInstallment = $feesInstallment;
        $this->schoolSettings = $schoolSettings;
        $this->medium = $medium;
        $this->feesType = $feesType;
        $this->classes = $classes;
        $this->feesClassType = $feesClassType;
        $this->user = $user;
        $this->feesPaid = $feesPaid;
        $this->compulsoryFee = $compulsoryFee;
        $this->optionalFee = $optionalFee;
        $this->userService = $userService;
        $this->cache = $cache;
        $this->paymentConfigurations = $paymentConfigurations;
        $this->class = $classSchool;
        $this->student = $student;
        $this->paymentTransaction = $paymentTransaction;
        $this->systemSetting = $systemSetting;
    }

    /* START : Fees Module */
    public function index()
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-list');
        $classes = $this->class->all(['*'], ['stream', 'medium', 'stream']);
        $feesTypeData = $this->feesType->all();
        $sessionYear = $this->sessionYear->builder()->pluck('name', 'id');
        $defaultSessionYear = $this->cache->getDefaultSessionYear();
        $mediums = $this->medium->builder()->pluck('name', 'id');
        return view('fees.index', compact('classes', 'feesTypeData', 'sessionYear', 'defaultSessionYear', 'mediums'));
    }

    public function store(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-create');
        $request->validate([
            'include_fee_installments'            => 'required|boolean',
            'due_date'                            => 'required|date',
            'due_charges_percentage'              => 'required|numeric',
            'due_charges_amount'                  => 'required|numeric',
            'class_id'                            => 'required|array',
            'class_id.*'                          => 'required|numeric',
            'compulsory_fees_type'                => 'required|array',
            'compulsory_fees_type.*'              => 'required|array',
            'compulsory_fees_type.*.fees_type_id' => 'required|numeric',
            'compulsory_fees_type.*.amount'       => 'required|numeric',
            'optional_fees_type.*'                => 'required|array',
            'optional_fees_type.*.fees_type_id'   => 'required|numeric',
            'optional_fees_type.*.amount'         => 'required|numeric',
            'fees_installments'                   => 'required_if:include_fee_installments,1|array',
            'fees_installments.*.name'            => 'required',
            'fees_installments.*.due_date'        => 'required|date',
            'fees_installments.*.due_charges'     => 'required|numeric'
        ]);
        try {
            DB::beginTransaction();
            $sessionYear = $this->cache->getDefaultSessionYear();
            $classes = $this->class->builder()->whereIn("id", $request->class_id)->with('stream', 'medium')->get();

            $notifyUser = $this->student->builder()->whereHas('class_section', function ($q) use ($request) {
                $q->whereIn('class_id', $request->class_id);
            })->pluck('guardian_id');

            $title = 'Fees';
            $body = $request->name;
            $type = 'Fees';
            send_notification($notifyUser, $title, $body, $type); // Send Notification

            foreach ($request->class_id as $class_id) {
                $class = $classes->first(function ($data) use ($class_id) {
                    return $data->id == $class_id;
                });
                $name = (!empty($request->name)) ? $request->name . " - " : "";
                $fees = $this->fees->create([
                    'name'               => $name . $class->full_name,
                    'due_date'           => $request->due_date,
                    'due_charges'        => $request->due_charges_percentage,
                    'due_charges_amount' => $request->due_charges_amount,
                    'class_id'           => $class_id,
                    'session_year_id'    => $sessionYear->id,
                ]);
                $feeClassType = [];
                foreach ($request->compulsory_fees_type as $data) {
                    $feeClassType[] = array(
                        "fees_id"      => $fees->id,
                        "class_id"     => $class_id,
                        "fees_type_id" => $data['fees_type_id'],
                        "amount"       => $data['amount'],
                        "optional"     => 0,
                    );
                }

                if (!empty($request->optional_fees_type)) {
                    foreach ($request->optional_fees_type as $data) {
                        $feeClassType[] = array(
                            "fees_id"      => $fees->id,
                            "class_id"     => $class_id,
                            "fees_type_id" => $data['fees_type_id'],
                            "amount"       => $data['amount'],
                            "optional"     => 1,
                        );
                    }
                }

                if (count($feeClassType) > 0) {
                    $this->feesClassType->upsert($feeClassType, ['class_id', 'fees_type_id'], ['amount', 'optional']);
                }

                if ($request->include_fee_installments && count($request->fees_installments)) {
                    $installmentData = array();
                    foreach ($request->fees_installments as $data) {
                        $data = (object)$data;
                        $installmentData[] = array(
                            'name'             => $data->name,
                            'due_date'         => date('Y-m-d', strtotime($data->due_date)),
                            'due_charges_type' => $data->due_charges_type,
                            'due_charges'      => $data->due_charges,
                            'fees_id'          => $fees->id,
                            'session_year_id'  => $sessionYear->id,
                        );
                    }
                    $this->feesInstallment->createBulk($installmentData);
                }
            }

            DB::commit();
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, "FeesController -> Store Method");
            ResponseService::errorResponse();
        }
    }

    public function show() {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-list');
        $offset = request('offset', 0);
        $limit = request('limit', 10);
        $sort = request('sort', 'id');
        $order = request('order', 'DESC');
        $search = request('search');
        $showDeleted = request('show_deleted');
        $session_year_id = request('session_year_id');
        $medium_id = request('medium_id');

        $sql = $this->fees->builder()->with('installments', 'class:id,name,stream_id,medium_id', 'class.medium:id,name', 'class.stream:id,name', 'fees_class_type.fees_type:id,name')
        ->where(function($q) use($search) {
            $q->when($search, function ($query) use ($search) {
                $query->where('id', 'LIKE', "%$search%")
                    ->orwhere('name', 'LIKE', "%$search%")
                    ->orwhere('due_date', 'LIKE', "%$search%")
                    ->orwhere('due_charges', 'LIKE', "%$search%");
            });
        })
            ->when(!empty($showDeleted), function ($query) {
                $query->onlyTrashed();
            })->when($session_year_id, function ($query) use($session_year_id) {
                $query->where('session_year_id',$session_year_id);
            })->when($medium_id, function ($query) use($medium_id) {
                $query->whereHas('class',function($q) use($medium_id) {
                    $q->where('medium_id',$medium_id);
                });
            });

        $total = $sql->count();

        $sql->orderBy($sort, $order)->skip($offset)->take($limit);
        $res = $sql->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($res as $row) {
            $operate = '';
            if ($showDeleted) {
                $operate .= BootstrapTableService::restoreButton(route('fees.restore', $row->id));
                $operate .= BootstrapTableService::trashButton(route('fees.trash', $row->id));
            } else {
                $operate .= BootstrapTableService::editButton(route('fees.edit', $row->id), false);
                $operate .= BootstrapTableService::deleteButton(route('fees.destroy', $row->id));
            }

            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['compulsory_fees'] = number_format($row->fees_class_type->filter(function ($data) {
                return $data->optional == 0;
            })->sum('amount'), 2);
            $tempRow['total_fees'] = number_format($row->fees_class_type->sum('amount'), 2);
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
    

    public function edit($id)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-edit');
        $classes = $this->class->all(['*'], ['stream', 'medium', 'stream']);
        $feesTypeData = $this->feesType->all();

        $fees = $this->fees->builder()->with(['fees_class_type', 'installments', 'class.medium'])->withCount('fees_paid')->findOrFail($id);
        return view('fees.edit', compact('classes', 'feesTypeData', 'fees'));
    }

    public function update(Request $request, $id)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-edit');

        $request->validate([
            'include_fee_installments'            => 'required|boolean',
            'due_date'                            => 'required|date',
            'due_charges_percentage'              => 'required|numeric',
            'due_charges_amount'                  => 'required|numeric',
            'compulsory_fees_type'                => 'required|array',
            'compulsory_fees_type.*'              => 'required|array',
            'compulsory_fees_type.*.fees_type_id' => 'required|numeric',
            'compulsory_fees_type.*.amount'       => 'required|numeric',
            'optional_fees_type.*'                => 'required|array',
            'optional_fees_type.*.fees_type_id'   => 'required|numeric',
            'optional_fees_type.*.amount'         => 'required|numeric',
            'fees_installments'                   => 'nullable|array',
            'fees_installments.*.name'            => 'required',
            'fees_installments.*.due_date'        => 'required|date',
            'fees_installments.*.due_charges'     => 'required|numeric'
        ]);
        try {
            DB::beginTransaction();
            $sessionYear = $this->cache->getDefaultSessionYear();

            // Fees Data Store
            $feesData = array(
                'name'               => $request->name,
                'due_date'           => $request->due_date,
                'due_charges'        => $request->due_charges_percentage,
                'due_charges_amount' => $request->due_charges_amount
            );
            $fees = $this->fees->update($id, $feesData);

            foreach ($request->compulsory_fees_type as $data) {
                $feeClassType[] = array(
                    "id"           => $data['id'],
                    "fees_id"      => $fees->id,
                    "class_id"     => $fees->class_id,
                    "fees_type_id" => $data['fees_type_id'],
                    "amount"       => $data['amount'],
                    "optional"     => 0,
                );
            }

            if (!empty($request->optional_fees_type)) {
                foreach ($request->optional_fees_type as $data) {
                    $feeClassType[] = array(
                        "id"           => $data['id'],
                        "fees_id"      => $fees->id,
                        "class_id"     => $fees->class_id,
                        "fees_type_id" => $data['fees_type_id'],
                        "amount"       => $data['amount'],
                        "optional"     => 1,
                    );
                }
            }

            if (isset($feeClassType)) {
                $this->feesClassType->upsert($feeClassType, ['id'], ['fees_type_id', 'amount', 'optional']);
            }

            if (!empty($request->fees_installments)) {
                $installmentData = array();
                foreach ($request->fees_installments as $data) {
                    $data = (object)$data;
                    $installmentData[] = array(
                        'id'               => $data->id,
                        'name'             => $data->name,
                        'due_date'         => date('Y-m-d', strtotime($data->due_date)),
                        'due_charges_type' => $data->due_charges_type,
                        'due_charges'      => $data->due_charges,
                        'fees_id'          => $fees->id,
                        'session_year_id'  => $sessionYear->id
                    );
                }

                $this->feesInstallment->upsert($installmentData, ['id'], ['name', 'due_date', 'due_charges', 'due_charges_type', 'fees_id', 'session_year_id']);
            }

            DB::commit();
            ResponseService::successRedirectResponse(route('fees.index'), 'Data Update Successfully');
        } catch (Throwable) {
            DB::rollback();
            ResponseService::errorRedirectResponse();
        }
    }

    public function destroy($id)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenSendJson('fees-delete');
        try {
            DB::beginTransaction();
            $this->fees->deleteById($id);
            DB::commit();
            ResponseService::successResponse("Data Deleted Successfully");
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "FeesController -> Store Method");
            ResponseService::errorResponse();
        }
    }

    public function restore(int $id)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-delete');
        try {
            $this->fees->findOnlyTrashedById($id)->restore();
            ResponseService::successResponse("Data Restored Successfully");
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e);
            ResponseService::errorResponse();
        }
    }

    public function search(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        try {
            $data = $this->fees->builder()->where('session_year_id', $request->session_year_id)->get();
            ResponseService::successResponse("Data Restored Successfully", $data);
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e);
            ResponseService::errorResponse();
        }
    }

    public function trash($id)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-delete');
        try {
            $this->fees->findOnlyTrashedById($id)->forceDelete();
            ResponseService::successResponse("Data Deleted Permanently");
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e);
            ResponseService::errorResponse();
        }
    }

    /* END : Fees Module */

    public function deleteInstallment($id)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        try {
            DB::beginTransaction();
            $this->feesInstallment->DeleteById($id);
            DB::commit();
            ResponseService::successResponse("Data Deleted Successfully");
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e);
            ResponseService::errorResponse();
        }
    }

    public function deleteClassType($id)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        try {
            DB::beginTransaction();
            $this->feesClassType->DeleteById($id);
            DB::commit();
            ResponseService::successResponse("Data Deleted Successfully");
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e);
            ResponseService::errorResponse();
        }
    }

    public function removeOptionalFees($id)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-paid');
        try {
            DB::beginTransaction();

            // Get Fees Paid ID and Amount of Fees Transaction Table
            $optionalFeeData = $this->optionalFee->findById($id);
            $feesPaidId = $optionalFeeData->fees_paid_id;
            $optionalFeeAmount = $optionalFeeData->amount;

            $this->optionalFee->permanentlyDeleteById($id); // Permanently Delete Optional Fees Data

            // Check Fees Transactions Entry
            $feesPaidDataQuery = $this->feesPaid->builder()->where('id', $feesPaidId);
            if ($feesPaidDataQuery->count()) {
                // Get Fees Paid Data
                $feesPaidAmount = $feesPaidDataQuery->first()->amount; // Get Fees Paid Amount
                $finalAmount = $feesPaidAmount - $optionalFeeAmount; // Calculate Final Amount
                if ($finalAmount > 0) {
                    $this->feesPaid->update($feesPaidId, ['amount' => $finalAmount]); // Update Fees Paid Data with Final Amount
                } else {
                    $this->feesPaid->permanentlyDeleteById($feesPaidId);
                }
            } else {
                $this->feesPaid->permanentlyDeleteById($feesPaidId);
            }

            DB::commit();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e);
            ResponseService::errorResponse();
        }
    }

    public function removeInstallmentFees($compulsoryFeesPaidID)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-paid');
        try {
            DB::beginTransaction();

            // Get Fees Paid ID and Amount of Fees Transaction Table
            $installmentFeeTransaction = $this->compulsoryFee->findById($compulsoryFeesPaidID);
            $feesPaidId = $installmentFeeTransaction->fees_paid_id;
            $feesTransactionAmount = $installmentFeeTransaction->amount;

            $this->compulsoryFee->permanentlyDeleteById($compulsoryFeesPaidID); // Permanently Delete Fees Transaction Data

            // Check Fees Transactions Entry
            $feesPaidDataQuery = $this->feesPaid->builder()->where('id', $feesPaidId);
            if ($feesPaidDataQuery->count()) {
                // Get Fees Paid Data
                $feesPaidAmount = $feesPaidDataQuery->first()->amount; // Get Fees Paid Amount
                $finalAmount = $feesPaidAmount - $feesTransactionAmount; // Calculate Final Amount
                if ($finalAmount > 0) {
                    $this->feesPaid->update($feesPaidId, ['amount' => $finalAmount, 'is_fully_paid' => 0]); // Update Fees Paid Data with Final Amount
                } else {
                    $this->feesPaid->permanentlyDeleteById($feesPaidId);
                }
            } else {
                $this->feesPaid->permanentlyDeleteById($feesPaidId);
            }

            DB::commit();
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e);
            ResponseService::errorResponse();
        }
    }

    public function feesConfigIndex()
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-config');

        // List of the names to be fetched
        $names = array('currency_code', 'currency_symbol',);

        $settings = $this->schoolSettings->getBulkData($names); // Passing the array of names and gets the array of data
        $domain = request()->getSchemeAndHttpHost(); // Get Current Web Domain

        $stripeData = $this->paymentConfigurations->all()->where('payment_method', 'stripe')->first();
        return view('fees.fees_config', compact('settings', 'domain', 'stripeData'));
    }

    public function feesConfigUpdate(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-config');
        $request->validate(['stripe_status' => 'required', 'stripe_publishable_key' => 'required_if:stripe_status,1|nullable', 'stripe_secret_key' => 'required_if:stripe_status,1|nullable', 'stripe_webhook_secret' => 'required_if:stripe_status,1|nullable', 'stripe_webhook_url' => 'required_if:stripe_status,1|nullable', 'currency_code' => 'required|max:10', 'currency_symbol' => 'required|max:5',]);
        try {
            $this->paymentConfigurations->updateOrCreate(['payment_method' => strtolower('stripe')], ['api_key' => $request->stripe_publishable_key, 'secret_key' => $request->stripe_secret_key, 'webhook_secret_key' => $request->stripe_webhook_secret, 'status' => $request->stripe_status]);


            // Store Currency Code and Currency Symbol in School Settings
            $settings = array('currency_code', 'currency_symbol');

            $data = array();
            foreach ($settings as $row) {
                $data[] = [
                    "name" => $row,
                    "data" => $row == 'school_name' ? str_replace('"', '', $request->$row) : $request->$row,
                    "type" => "string"
                ];
            }

            $this->schoolSettings->upsert($data, ["name"], ["data"]);
            Cache::flush();

            ResponseService::successResponse('Data Updated Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e);
            ResponseService::errorResponse();
        }
    }

    public function feesTransactionsLogsIndex()
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-paid');
        $session_year_all = $this->sessionYear->all(['id', 'name', 'default']);
        $classes = $this->classes->builder()->orderByRaw('CONVERT(name, SIGNED) asc')->with('medium', 'stream', 'sections')->get();
        $mediums = $this->medium->builder()->orderBy('id', 'ASC')->get();

        $months = sessionYearWiseMonth();

        return response(view('fees.fees_transaction_logs', compact('classes', 'mediums', 'session_year_all', 'months')));
    }

public function feesPaidListIndex(Request $request)
{
    ResponseService::noFeatureThenRedirect('Fees Management');
    ResponseService::noPermissionThenRedirect('fees-paid');

    $fees = $this->fees->builder()
        ->select(['id', 'name'])
        ->get();

    $classes = $this->classes->all(['*'], ['medium', 'sections']);

    $session_year_all = $this->sessionYear->all(['id', 'name', 'default']);

    $months = sessionYearWiseMonth();

    $sessionYearId = $request->session_year_id
        ?? $this->cache->getDefaultSessionYear()->id;

    $feesId = $request->fees_id;

    /*
    |--------------------------------------------------------------------------
    | Dashboard Statistics
    |--------------------------------------------------------------------------
    |
    | Calculate directly from invoice items.
    |
    */

    $itemsQuery = DB::table('student_fee_invoice_items as item')
        ->join(
            'student_fee_invoices as invoice',
            'invoice.id',
            '=',
            'item.student_fee_invoice_id'
        )
        ->where('invoice.session_year_id', $sessionYearId);

    if (!empty($feesId)) {
        $itemsQuery->where('item.fees_id', $feesId);
    }

    $totalInvoiced = (clone $itemsQuery)->sum('item.amount');

    $totalPaid = (clone $itemsQuery)->sum('item.paid_amount');

    $totalPending = (clone $itemsQuery)->sum('item.balance');

    /*
    |--------------------------------------------------------------------------
    | Invoice Counts
    |--------------------------------------------------------------------------
    */

    $invoiceQuery = StudentFeeInvoice::where(
        'session_year_id',
        $sessionYearId
    );

    if (!empty($feesId)) {

        $invoiceQuery->whereHas('items', function ($q) use ($feesId) {

            $q->where('fees_id', $feesId);

        });
    }

    $fullyPaid = (clone $invoiceQuery)
        ->where('status', 'paid')
        ->count();

    $defaulters = (clone $invoiceQuery)
        ->whereIn('status', ['partial', 'unpaid'])
        ->count();

    $collectionRate = $totalInvoiced > 0
        ? round(($totalPaid / $totalInvoiced) * 100, 2)
        : 0;

    $stats = [

        'total_invoiced'  => $totalInvoiced,

        'total_paid'      => $totalPaid,

        'total_pending'   => $totalPending,

        'fully_paid'      => $fullyPaid,

        'defaulters'      => $defaulters,

        'collection_rate' => $collectionRate,

    ];

    /*
    |--------------------------------------------------------------------------
    | Fee Breakdown
    |--------------------------------------------------------------------------
    |
    | Group by Fee Structure (School Fees, Snacks, Transport, etc.)
    | Uses invoice items only.
    |
    */

    /*
|--------------------------------------------------------------------------
| Fee Breakdown (By Fee Type)
|--------------------------------------------------------------------------
*/

$feeBreakdown = DB::table('fees_types as ft')

    ->leftJoin('student_fee_invoice_items as item', function ($join) {
        $join->on('ft.id', '=', 'item.fees_type_id');
    })

    ->leftJoin('student_fee_invoices as invoice', function ($join) use ($sessionYearId) {

        $join->on('invoice.id', '=', 'item.student_fee_invoice_id')
             ->where('invoice.session_year_id', $sessionYearId);

    })

    ->when($feesId, function ($query) use ($feesId) {

        $query->where('item.fees_id', $feesId);

    })

    ->select(

        'ft.id',
        'ft.name',

        DB::raw('COALESCE(SUM(item.amount),0) as invoiced'),

        DB::raw('COALESCE(SUM(item.paid_amount),0) as paid'),

        DB::raw('COALESCE(SUM(item.balance),0) as balance'),

        DB::raw('COUNT(item.id) as items')

    )

    ->groupBy('ft.id', 'ft.name')

    ->orderBy('ft.name')

    ->get();
    return view(
        'fees.fees_paid',
        compact(
            'fees',
            'classes',
            'session_year_all',
            'months',
            'stats',
            'feeBreakdown'
        )
    );
}



    public function feesPaidList(Request $request)
    {

        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-paid');

        $offset = $request->offset ?? 0;
        $limit  = $request->limit ?? 10;
        $sort   = $request->sort ?? 'id';
        $order  = $request->order ?? 'DESC';

        $sessionYearId = $request->session_year_id
            ?: $this->cache->getDefaultSessionYear()->id;

        $feesId = $request->fees_id;
        $status = $request->paid_status;
        $search = $request->search;

        $query = StudentFeeInvoice::owner()

            ->with([
                'student.user',
                'student.class_section.class',
                'student.class_section.section',
                'items.fee',
                'items.feeType',
                'payments'
            ])

            ->where('session_year_id', $sessionYearId);
        if (!empty($search)) {

            $query->where(function ($q) use ($search) {

                $q->where('invoice_no', 'like', "%{$search}%")

                    ->orWhereHas('student.user', function ($user) use ($search) {

                        $user->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })

                    ->orWhereHas('student', function ($student) use ($search) {

                        $student->where('admission_no', 'like', "%{$search}%");
                    });
            });
        }
        if (!empty($feesId)) {

            $query->whereHas('items', function ($q) use ($feesId) {

                $q->where('fees_id', $feesId);
            });
        }
        if ($status !== null && $status !== '') {

            switch ($status) {

                case 0:

                    $query->whereIn('status', ['unpaid', 'partial']);

                    break;

                case 1:

                    $query->where('status', 'paid');

                    break;
            }
        }
        $total = $query->count();
        $invoices = $query

            ->orderBy($sort, $order)

            ->skip($offset)

            ->take($limit)

            ->get();

        $bulkData = [];

        $bulkData['total'] = $total;

        $rows = [];

        $no = $offset + 1;
        foreach ($invoices as $invoice) {

            $student = $invoice->student;
            $user = optional($student)->user;
            $classSection = optional($student)->class_section;

            $className = '';

            if ($classSection) {
                $className =
                    optional($classSection->class)->name .
                    ' ' .
                    optional($classSection->section)->name;
            }

            /*
    |--------------------------------------------------------------------------
    | Fee Names
    |--------------------------------------------------------------------------
    */

            $feeNames = $invoice->items
                ->pluck('fee.name')
                ->filter()
                ->unique()
                ->implode(', ');

            /*
    |--------------------------------------------------------------------------
    | Fee Categories
    |--------------------------------------------------------------------------
    */

            $feeTypes = $invoice->items
                ->pluck('feeType.name')
                ->filter()
                ->unique()
                ->implode(', ');

            /*
    |--------------------------------------------------------------------------
    | Status Badge
    |--------------------------------------------------------------------------
    */

            switch ($invoice->status) {

                case 'paid':

                    $status = '<span class="badge badge-success">PAID</span>';

                    break;

                case 'partial':

                    $status = '<span class="badge badge-warning">PARTIAL</span>';

                    break;

                default:

                    $status = '<span class="badge badge-danger">UNPAID</span>';
            }

            /*
    |--------------------------------------------------------------------------
    | Action Menu
    |--------------------------------------------------------------------------
    */

            $operate = '<div class="dropdown">

<button class="btn btn-sm btn-primary dropdown-toggle d-flex align-items-center gap-1"
    data-toggle="dropdown">
    <i class="fa fa-file-text"></i>
    <span class="caret"></span>
</button>


<div class="dropdown-menu">

<a class="dropdown-item"
   target="_blank"
   href="' . route('fees.statement', $invoice->student_id) . '">

<i class="fa fa-eye text-info"></i>
Statement
</a>

<a class="dropdown-item"
   target="_blank"
   href="' . route('fees.statement.pdf', $invoice->student_id) . '">

<i class="fa fa-file-pdf-o text-danger"></i>
Download PDF
</a>

</div>

</div>';

            /*
    |--------------------------------------------------------------------------
    | Table Row
    |--------------------------------------------------------------------------
    */

            $rows[] = [

                'id' => $invoice->id,

                'no' => $no++,

                'invoice_no' => $invoice->invoice_no,

                'student_name' => optional($user)->full_name,

                'admission_no' => optional($student)->admission_no,

                'class' => $className,

                'fees' => $feeNames,

                'fee_types' => $feeTypes,

                'total_amount' => number_format($invoice->total_amount, 2),

                'paid_amount' => number_format($invoice->paid_amount, 2),

                'balance' => number_format($invoice->balance, 2),

                'due_date' => $invoice->due_date
                    ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d')
                    : '-',

                'status' => $status,

                'operate' => $operate,

            ];
        }
        $bulkData['rows'] = $rows;


        return response()->json($bulkData);
    }






public function studentStatement($studentId)
{
    $student = Students::with([
        'user',
        'class_section.class',
        'class_section.section'
    ])->findOrFail($studentId);

$invoices = StudentFeeInvoice::with([
    'items',
    'items.feeType',
    'items.payments.user',
    'items.creditTransactions.user',
])
->where('student_id', $student->id)
->latest()
->get();

 $school = $this->cache->getSchoolSettings();


    // Refresh totals from invoice items
    foreach ($invoices as $invoice) {
        $this->refreshInvoice($invoice);
    }

    $summary = $this->calculateStatementSummary($student, $invoices);

    $ledger = $this->buildStatementLedger($invoices);

    return view(
        'fees.statement',
        compact(
            'student',
            'invoices',
            'summary',
            'ledger',
            'school'
        )
    );
}

private function calculateStatementSummary($student, $invoices): array
{
    return [

        'total_invoiced' => $invoices->sum('total_amount'),

        'total_paid' => $invoices->sum('paid_amount'),

        'balance' => $invoices->sum('balance'),

        'credit_balance' => $student->credit_balance,

    ];
}

private function buildStatementLedger($invoices)
{
    $ledger = collect();

    foreach ($invoices as $invoice) {

        $ledger->push([

            'date' => $invoice->created_at,

            'type' => 'invoice',

            'invoice' => $invoice,

        ]);

        foreach ($invoice->items as $item) {

            foreach ($item->payments as $payment) {

                $ledger->push([

                    'date' => $payment->payment_date
                        ? \Carbon\Carbon::parse($payment->payment_date)
                        : $payment->created_at,

                    'type' => 'payment',

                    'invoice' => $invoice,

                    'item' => $item,

                    'payment' => $payment,

                ]);
            }
        }
    }

    return $ledger->sortBy('date')->values();
}


    public function viewInvoice($invoiceId)
    {
        $invoice = StudentFeeInvoice::with([
            'student.user',
            'student.class_section.class',
            'student.class_section.section',
            'items.feeType',
            'payments'
        ])->findOrFail($invoiceId);


        return view('fees.invoice', compact('invoice'));
    }



    public function studentStatementPdf($studentId)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-paid');

        try {

            $student = $this->student->builder()
                ->with([
                    'user:id,first_name,last_name',
                    'class_section.class.stream',
                    'class_section.section',
                    'class_section.medium'
                ])
                ->whereHas('user', function ($q) use ($studentId) {
                    $q->where('id', $studentId);
                })
                ->firstOrFail();

            $invoices = StudentFeeInvoice::with([
                'items.feeType',
                'payments'
            ])
                ->where('student_id', $studentId)
                ->latest()
                ->get();

            $summary = [
                'total_invoiced' => $invoices->sum('total_amount'),
                'total_paid'     => $invoices->sum('paid_amount'),
                'balance'        => $invoices->sum('balance'),
            ];

            $systemVerticalLogo = $this->systemSetting->builder()
                ->where('name', 'vertical_logo')
                ->first();

            $schoolVerticalLogo = $this->schoolSettings->builder()
                ->where('name', 'vertical_logo')
                ->first();

            $school = $this->cache->getSchoolSettings();

            $pdf = Pdf::loadView('fees.student_statement', compact(
                'student',
                'invoices',
                'summary',
                'systemVerticalLogo',
                'schoolVerticalLogo',
                'school'
            ));

            return $pdf->stream('student-statement.pdf');
        } catch (Throwable $e) {
            report($e);
            return ResponseService::errorRedirectResponse();
        }
    }

    public function generateInvoice()
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-paid');

        $session_year_all = $this->sessionYear
            ->builder()
            ->orderBy('id', 'DESC')
            ->get();

        $classes = $this->classes
            ->builder()
            ->with('sections')
            ->orderBy('name')
            ->get();
        $fees = $this->feesClassType
            ->builder()
            ->with('fees_type')
            ->orderBy('class_id')
            ->orderBy('fees_id')
            ->get();

        $semesters = Semester::orderBy('name')->get();

        $feeStructures = FeesClassType::owner()
            ->with('fees_type')
            ->orderBy('class_id')
            ->orderBy('fees_id')
            ->get();





        return view(
            'fees.generate_invoice',
            compact(
                'session_year_all',
                'classes',
                'semesters',
                'feeStructures',
                'fees'
            )
        );
    }


public function storeGeneratedInvoice(Request $request)
{
    $request->validate([

        'session_year_id' => 'required|exists:session_years,id',
        'semester_id'     => 'required|exists:semesters,id',

        'students' => 'required|array|min:1',

        'students.*.id'   => 'required|exists:students,id',
        'students.*.fees' => 'required|array|min:1',

    ]);

    DB::beginTransaction();

    try {

        $created = 0;
        $skipped = [];

        foreach ($request->students as $studentData) {

            $student = Students::findOrFail($studentData['id']);

            $exists = StudentFeeInvoice::where('student_id', $student->id)
                ->where('session_year_id', $request->session_year_id)
                ->where('semester_id', $request->semester_id)
                ->exists();

            if ($exists) {

                $skipped[] = $student->admission_no;

                continue;
            }

            $total = collect($studentData['fees'])->sum(function ($fee) {
                return (float) $fee['amount'];
            });


            $invoice = StudentFeeInvoice::create([

                'student_id'      => $student->id,

                'session_year_id' => $request->session_year_id,

                'semester_id'     => $request->semester_id,

                'invoice_no'      => $this->userService->generateInvoiceNo(),

                'school_id'       => $student->school_id,

                'generated_by'    => auth()->id(),

                'total_amount'    => $total,

                'paid_amount'     => 0,

                'balance'         => $total,

                'status'          => 'unpaid',

            ]);

            foreach ($studentData['fees'] as $fee) {

                StudentFeeInvoiceItem::create([

                    'student_fee_invoice_id' => $invoice->id,

                    'fees_id'                => $fee['fees_id'],

                    'fees_type_id'           => $fee['fees_type_id'],

                    'amount'                 => $fee['amount'],

                    'paid_amount'            => 0,

                    'balance'                => $fee['amount'],

                ]);
            }

            $this->applyStudentCredit($student);
            $this->refreshInvoice($invoice);

            $created++;
        }

        DB::commit();

        return response()->json([

            'success' => true,

            'message' => $created . ' invoice(s) generated successfully.',

            'created' => $created,

            'skipped' => $skipped

        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        return response()->json([

            'success' => false,

            'message' => $e->getMessage()

        ], 500);
    }
}


private function applyStudentCredit(Students $student): void
{
    if ($student->credit_balance <= 0) {
        return;
    }

    $credit = $student->credit_balance;

    $updatedInvoices = [];

    $invoices = StudentFeeInvoice::with([
            'items' => function ($q) {
                $q->where('balance', '>', 0)
                  ->orderBy('id');
            }
        ])
        ->where('student_id', $student->id)
        ->where('balance', '>', 0)
        ->orderBy('id')
        ->get();

    foreach ($invoices as $invoice) {

        foreach ($invoice->items as $item) {

            if ($credit <= 0) {
                break 2;
            }

            $apply = min($credit, $item->balance);

            if ($apply <= 0) {
                continue;
            }

            $item->paid_amount += $apply;
            $item->balance -= $apply;

            if ($item->balance < 0) {
                $item->balance = 0;
            }

            $item->save();

            $credit -= $apply;

            $updatedInvoices[$invoice->id] = $invoice;

            StudentCreditTransaction::create([

                'student_id'             => $student->id,

                'payment_transaction_id' => null,

                'student_fee_invoice_id' => $invoice->id,

                'student_fee_invoice_item_id' => $item->id,

                'user_id'                => auth()->id(),

                'type'                   => 'invoice_application',

                'amount'                 => -$apply,

                'balance_after'          => $credit,

                'reference'              => $invoice->invoice_no,

                'remarks'                => 'Automatically applied existing student credit',

            ]);
        }
    }

    foreach ($updatedInvoices as $updatedInvoice) {

        $this->refreshInvoice($updatedInvoice);

    }
    $student->credit_balance = $credit;
    $student->save();
}


private function refreshInvoice(StudentFeeInvoice $invoice)
{
    $invoice->paid_amount = StudentFeeInvoiceItem::where(
        'student_fee_invoice_id',
        $invoice->id
    )->sum('paid_amount');

    $invoice->balance = StudentFeeInvoiceItem::where(
        'student_fee_invoice_id',
        $invoice->id
    )->sum('balance');

    if ($invoice->balance <= 0) {
        $invoice->balance = 0;
        $invoice->status = 'paid';
    } elseif ($invoice->paid_amount > 0) {
        $invoice->status = 'partial';
    } else {
        $invoice->status = 'unpaid';
    }

    $invoice->save();
}



public function searchStudentForManualPayment(Request $request)
{
    $search = trim($request->search);

    $students = Students::with([
        'user',
        'class_section.class',
        'class_section.section',
    ])
    ->withCount([
        'invoices as open_invoices_count' => function ($q) {
            $q->where('balance', '>', 0);
        }
    ])
    ->withSum([
        'invoices as outstanding_balance' => function ($q) {
            $q->where('balance', '>', 0);
        }
    ], 'balance')
    ->withSum('invoices as total_invoiced', 'total_amount')
    ->withSum('invoices as total_paid', 'paid_amount')
    ->where(function ($query) use ($search) {

        $query->where('admission_no', 'like', "%{$search}%")
            ->orWhereHas('user', function ($q) use ($search) {

                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");

            });

    })
    ->limit(10)
    ->get();



    return view(
        'fees.partials.search_students_payment',
        compact('students')
    )->render();
}


    public function searchStudentForManualPaymentIndex()
    {
        return view('fees.receive_payment');
    }


    public function receivePaymentManual($student)
    {
        $student = Students::with([
            'user',
            'class_section.class',
            'class_section.section'
        ])->findOrFail($student);


$outstandingItems = StudentFeeInvoiceItem::with('fee')
    ->whereHas('invoice', function ($query) use ($student) {

        $query->where('student_id', $student->id);

    })
    ->where('balance', '>', 0)
    ->get();



        /*
    |--------------------------------------------------------------------------
    | Total outstanding balance
    |--------------------------------------------------------------------------
    */

        $outstandingBalance = $outstandingItems->sum('balance');
        $netBalance = $outstandingBalance - ($student->credit_balance ?? 0);




        /*
    |--------------------------------------------------------------------------
    | Group outstanding balances by fee type
    |--------------------------------------------------------------------------
    */

        $feeItems = $outstandingItems

            ->groupBy('fees_id')

            ->map(function ($items) {

                return (object)[

                    'fees_id' => $items->first()->fees_id,

                    'balance' => $items->sum('balance'),

                    'fee' => $items->first()->fee

                ];
            })

            ->values();



        return view('fees.receive_individual_payment', compact(

            'student',

            'outstandingBalance',

            'feeItems',
            'netBalance'

        ));
    }


public function storeManualPayment(Request $request)
{
    $request->validate([
        'student_id' => 'required|exists:students,id',
        'amount'     => 'required|numeric|min:1',
        'cheque_no'  => 'required|string',
        'cheque_date'=> 'required|date',
    ]);

    try {

        DB::transaction(function () use ($request) {

            $student = Students::findOrFail($request->student_id);

            /*
            |--------------------------------------------------------------------------
            | Shared Order ID For Entire Cheque
            |--------------------------------------------------------------------------
            */

            $orderId = 'BANK-' .
                now()->format('YmdHis') .
                '-' .
                strtoupper(Str::random(6));

            $allocatedTotal = 0;

            /*
            |--------------------------------------------------------------------------
            | Allocate Each Fee Category
            |--------------------------------------------------------------------------
            |
            | allocations[] is keyed by fees_id.
            | The helper method will automatically:
            | - Pay oldest invoice first (FIFO)
            | - Continue to next invoice if necessary
            | - Create one PaymentTransaction per invoice item
            | - Update invoice items
            | - Update invoice totals
            | - Create StudentCreditTransaction
            |
            */

            foreach ($request->allocations ?? [] as $feeId => $amount) {

                if ($amount <= 0) {
                    continue;
                }

                $allocated = $this->allocateFeeCategory(

                    student: $student,

                    feeId: $feeId,

                    allocationAmount: $amount,

                    request: $request,

                    orderId: $orderId

                );

                $allocatedTotal += $allocated;
            }

            /*
            |--------------------------------------------------------------------------
            | Store Remaining Amount As Credit
            |--------------------------------------------------------------------------
            */

            $remaining = round($request->amount - $allocatedTotal, 2);

            if ($remaining > 0) {

                $student->credit_balance += $remaining;

                $student->save();

                /*
                |--------------------------------------------------------------------------
                | Payment Transaction For Credit
                |--------------------------------------------------------------------------
                */

                $payment = PaymentTransaction::create([

                    'student_id'                  => $student->id,

                    'student_fee_invoice_id'      => null,

                    'student_fee_invoice_item_id' => null,

                    'amount'                      => $remaining,

                    'payment_gateway'             => 'Bank Deposit',

                    'payment_id'                  => $request->cheque_no,

                    'order_id'                    => $orderId,

                    'payment_signature'           => auth()->user()->first_name .
                                                     ' ' .
                                                     auth()->user()->last_name,

                    'payment_status'              => 'completed',

                    'bank_name'                   => $request->bank_name,

                    'payment_date'                => $request->cheque_date,

                    'received_by'                 => auth()->user()->first_name .
                                                     ' ' .
                                                     auth()->user()->last_name,

                    'user_id'                     => auth()->id(),

                    'school_id'                   => auth()->user()->school_id,

                ]);

                /*
                |--------------------------------------------------------------------------
                | Credit Ledger
                |--------------------------------------------------------------------------
                */

                StudentCreditTransaction::create([

                    'student_id'             => $student->id,

                    'payment_transaction_id' => $payment->id,

                    'student_fee_invoice_id' => null,

                    'user_id'                => auth()->id(),

                    'type'                   => 'overpayment',

                    'amount'                 => $remaining,

                    'balance_after'          => $student->credit_balance,

                    'reference'              => $request->cheque_no,

                    'remarks'                => 'Excess cheque amount stored as student credit',

                ]);
            }
        });

        return redirect()
            ->route('fees.paid.receive.payment.manual', $request->student_id)
            ->with('success', 'Cheque payment recorded successfully.');

    } catch (\Throwable $e) {

        return redirect()
            ->route('fees.paid.receive.payment.manual', $request->student_id)
            ->with('error', $e->getMessage());
    }
}

private function allocateFeeCategory(
    Students $student,
    int $feeId,
    float $allocationAmount,
    Request $request,
    string $orderId
): float {

    $allocated = 0;

    $items = StudentFeeInvoiceItem::with([
            'invoice', 
            'fee'
        ])
        ->where('fees_id', $feeId)
        ->where('balance', '>', 0)
        ->whereHas('invoice', function ($q) use ($student) {

            $q->where('student_id', $student->id);

        })
        ->orderBy('student_fee_invoice_id')
        ->orderBy('id')
        ->get();

    $remaining = $allocationAmount;

    foreach ($items as $item) {

        if ($remaining <= 0) {
            break;
        }

        $payAmount = min($remaining, $item->balance);


        $item->paid_amount += $payAmount;

        $item->balance -= $payAmount;

        if ($item->balance < 0) {
            $item->balance = 0;
        }

        $item->save();

        $invoice = $item->invoice;

        $payment = PaymentTransaction::create([

            'student_id'                   => $student->id,

            'student_fee_invoice_id'       => $invoice->id,

            'student_fee_invoice_item_id'  => $item->id,

            'amount'                       => $payAmount,

            'payment_gateway'              => 'Bank Deposit',

            'payment_id'                   => $request->cheque_no,

            'order_id'                     => $orderId,

            'payment_signature'            => auth()->user()->first_name .
                                              ' ' .
                                              auth()->user()->last_name,

            'payment_status'               => 'completed',

            'bank_name'                    => $request->bank_name,

            'payment_date'                 => $request->cheque_date,

            'received_by'                  => auth()->user()->first_name .
                                              ' ' .
                                              auth()->user()->last_name,

            'user_id'                      => auth()->id(),

            'school_id'                    => auth()->user()->school_id,

        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Invoice Totals
        |--------------------------------------------------------------------------
        */

        $this->updateInvoiceTotals($invoice);

        $allocated += $payAmount;

        $remaining -= $payAmount;
    }

    return $allocated;
}

private function updateInvoiceTotals(StudentFeeInvoice $invoice): void
{
    /*
    |--------------------------------------------------------------------------
    | Recalculate Totals From Invoice Items
    |--------------------------------------------------------------------------
    */

    $invoice->refresh();

    $totalAmount = $invoice->items()->sum('amount');

    $totalPaid = $invoice->items()->sum('paid_amount');

    $totalBalance = $invoice->items()->sum('balance');

    $invoice->total_amount = $totalAmount;

    $invoice->paid_amount = $totalPaid;

    $invoice->balance = max($totalBalance, 0);

    /*
    |--------------------------------------------------------------------------
    | Determine Status
    |--------------------------------------------------------------------------
    */

    if ($invoice->balance <= 0) {

        $invoice->status = 'paid';

    } elseif ($invoice->paid_amount > 0) {

        $invoice->status = 'partial';

    } else {

        $invoice->status = 'unpaid';
    }

    /*
    |--------------------------------------------------------------------------
    | Record Last User Who Updated Invoice
    |--------------------------------------------------------------------------
    */

    $invoice->generated_by = auth()->id();

    $invoice->save();
}


    public function create(Students $student)
    {

        $invoiceItems = StudentFeeInvoiceItem::whereHas(
            'invoice',
            function ($q) use ($student) {

                $q->where('student_id', $student->id);
            }
        )
            ->whereColumn(
                'amount',
                '>',
                DB::raw('paid_amount')
            )
            ->get();


        return view(
            'fees.receive_payment',
            compact(
                'student',
                'invoiceItems'
            )
        );
    }




    public function feesPaidReceiptPDF($feesPaidId)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-paid');

        try {

            $feesPaid = $this->feesPaid->builder()->where('id', $feesPaidId)->with([
                'fees.fees_class_type.fees_type',
                'compulsory_fee.installment_fee:id,name',
                'optional_fee' => function ($q) {
                    $q->with(['fees_class_type' => function ($q) {
                        $q->select('id', 'fees_type_id')
                            ->with('fees_type:id,name');
                    }]);
                }
            ])->firstOrFail();

            $student = $this->student->builder()
                ->with('user:id,first_name,last_name', 'class_section.class.stream', 'class_section.section', 'class_section.medium')
                ->whereHas('user', function ($q) use ($feesPaid) {
                    $q->where('id', $feesPaid->student_id);
                })
                ->firstOrFail();

            $systemVerticalLogo = $this->systemSetting->builder()
                ->where('name', 'vertical_logo')
                ->first();

            $schoolVerticalLogo = $this->schoolSettings->builder()
                ->where('name', 'vertical_logo')
                ->first();

            $school = $this->cache->getSchoolSettings();

            $pdf = Pdf::loadView('fees.fees_receipt', compact(
                'systemVerticalLogo',
                'school',
                'feesPaid',
                'student',
                'schoolVerticalLogo'
            ));

            return $pdf->stream('fees-receipt.pdf');
        } catch (Throwable $e) {
            report($e);
            return ResponseService::errorRedirectResponse();
        }
    }


    public function payCompulsoryFeesIndex($feesID, $studentID)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        //        ResponseService::noPermissionThenRedirect('fees-edit');
        $fees = $this->fees->findById($feesID, ['*'], ['fees_class_type.fees_type:id,name', 'installments:id,name,due_date,due_charges,due_charges_type,fees_id']);
        $oneInstallmentPaid = false;

        $student = $this->user->builder()->role('Student')->select('id', 'first_name', 'last_name')
            ->with(['student' => function ($query) {
                $query->select('id', 'class_section_id', 'user_id', 'guardian_id')->with(['class_section' => function ($query) {
                    $query->select('id', 'class_id', 'section_id', 'medium_id')->with('class:id,name', 'section:id,name', 'medium:id,name');
                }]);
            }, 'fees_paid'    => function ($q) use ($feesID) {
                $q->where('fees_id', $feesID)->first();
            }, 'compulsory_fees.advance_fees'])->findOrFail($studentID);

        if (!empty($student->fees_paid) && $student->fees_paid->is_fully_paid) {
            ResponseService::successRedirectResponse(route('fees.paid.index'), 'Compulsory Fees Already Paid');
        }

        if (count($fees->installments) > 0) {
            $totalFeesAmount = $fees->total_compulsory_fees;
            $totalInstallments = count($fees->installments);

            collect($fees->installments)->map(function ($installment) use ($student, &$totalFeesAmount, &$totalInstallments, $fees, &$oneInstallmentPaid) {

                $installmentPaid = $student->compulsory_fees->first(function ($compulsoryFees) use ($installment) {
                    return $compulsoryFees->installment_id == $installment->id;
                });

                if (!empty($installmentPaid)) {
                    // Removing the Paid installments from total installments so that minimum amount can be calculated for the remaining installments.
                    --$totalInstallments;
                    $oneInstallmentPaid = true;
                    $totalFeesAmount -= $installmentPaid->amount;
                    $installment['is_paid'] = (object)$installmentPaid->toArray();
                    $installment['minimum_amount'] = $totalFeesAmount / $totalInstallments;
                    $installment['maximum_amount'] = $totalFeesAmount;
                } else {
                    $installment['is_paid'] = [];
                    $installment['minimum_amount'] = $totalFeesAmount / $totalInstallments;
                    $installment['maximum_amount'] = $totalFeesAmount;
                }
                if (new DateTime(date('Y-m-d')) > new DateTime($installment['due_date'])) {
                    if ($installment->due_charges_type == "percentage") {
                        $installment['due_charges_amount'] = ($installment['minimum_amount'] * $installment['due_charges']) / 100;
                    } else if ($installment->due_charges_type == "fixed") {
                        $installment['due_charges_amount'] = $installment->due_charges;
                    }
                } else {
                    $installment['due_charges_amount'] = 0;
                }

                $installment['total_amount'] = $installment['minimum_amount'] + $installment['due_charges_amount'];
                $fees->remaining_amount = $totalFeesAmount;
                return $installment;
            });
        }

        $currencySymbol = $this->cache->getSchoolSettings('currency_symbol');
        return view('fees.pay-compulsory', compact('fees', 'student', 'oneInstallmentPaid', 'currencySymbol'));
    }

    public function payCompulsoryFeesStore(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-paid');

        $request->validate([
            'fees_id'       => 'required|numeric',
            'student_id'    => 'required|numeric',
            'payment_amount' => 'required|numeric|min:0.01',
            'date'          => 'required',
            'mode'          => 'required|numeric'
        ]);

        try {
            DB::beginTransaction();

            $fees = $this->fees->findById(
                $request->fees_id,
                ['*'],
                ['fees_class_type.fees_type:id,name']
            );

            // Check if already fully paid
            $feesPaid = $this->feesPaid->builder()->where([
                'fees_id'    => $request->fees_id,
                'student_id' => $request->student_id
            ])->first();

            if (!empty($feesPaid) && $feesPaid->is_fully_paid) {
                ResponseService::errorResponse("Compulsory Fees already Paid");
            }

            $paymentAmount = (float) $request->payment_amount;

            $existingPaidAmount = !empty($feesPaid) ? $feesPaid->amount : 0;

            $totalPaid = $existingPaidAmount + $paymentAmount;

            $isFullyPaid = $totalPaid >= $fees->total_compulsory_fees;

            // Create or update fees_paid record
            if (empty($feesPaid)) {

                $feesPaidResult = $this->feesPaid->create([
                    'date'                => date('Y-m-d', strtotime($request->date)),
                    'is_fully_paid'       => $isFullyPaid,
                    'is_used_installment' => 0,
                    'fees_id'             => $request->fees_id,
                    'student_id'          => $request->student_id,
                    'amount'              => $paymentAmount,
                ]);
            } else {

                $feesPaidResult = $this->feesPaid->update($feesPaid->id, [
                    'amount'        => $totalPaid,
                    'is_fully_paid' => $isFullyPaid
                ]);
            }

            // Create single payment record
            $compulsoryFeeData = [
                'type'         => 'Manual Payment',
                'student_id'   => $request->student_id,
                'mode'         => $request->mode,
                'cheque_no'    => $request->mode == 2 ? $request->cheque_no : null,
                'amount'       => $paymentAmount,
                'fees_paid_id' => $feesPaidResult->id,
                'date'         => date('Y-m-d', strtotime($request->date))
            ];

            $this->compulsoryFee->create($compulsoryFeeData);

            DB::commit();

            ResponseService::successResponse("Payment Recorded Successfully");
        } catch (Throwable $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, 'FeesController -> payCompulsoryFeesStore');
            ResponseService::errorResponse();
        }
    }

    public function payOptionalFeesIndex($feesID, $studentID)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        //        ResponseService::noPermissionThenRedirect('fees-edit');
        $fees = $this->fees->findById($feesID, ['*'], ['fees_class_type.fees_type:id,name', 'installments:id,name,due_date,due_charges,fees_id']);

        $student = $this->user->builder()->role('Student')->select('id', 'first_name', 'last_name')
            ->with(['student' => function ($query) {
                $query->select('id', 'class_section_id', 'user_id', 'session_year_id')->with(['class_section' => function ($query) {
                    $query->select('id', 'class_id', 'section_id', 'medium_id')->with('class:id,name', 'section:id,name', 'medium:id,name');
                }]);
            }, 'fees_paid'    => function ($q) use ($feesID) {
                $q->where('fees_id', $feesID)->first();
            }])->findOrFail($studentID);


        $optionalFeesData = $this->feesClassType->builder()
            ->where(['class_id' => $student->student->class_section->class_id, 'optional' => 1])
            ->with([
                'fees_type',
                'optional_fees_paid' => function ($query) use ($student) {
                    $query->where('student_id', $student->id)->whereHas('fees_paid', function ($subQuery1) use ($student) {
                        $subQuery1->whereHas('fees', function ($subQuery2) use ($student) {
                            $subQuery2->where('session_year_id', $student->student->session_year_id);
                        });
                    });
                }
            ])
            ->get();

        return view('fees.pay-optional', compact('fees', 'student', 'optionalFeesData'));
    }

    public function payOptionalFeesStore(Request $request)
    {
        ResponseService::noFeatureThenRedirect('Fees Management');
        ResponseService::noPermissionThenRedirect('fees-paid');
        $request->validate([
            'fees_id'    => 'required|numeric',
            'student_id' => 'required|numeric',
        ]);
        try {
            DB::beginTransaction();

            // First Store in Fees Paid table to get Fees Paid ID
            $feesPaid = $this->feesPaid->builder()->where([
                'fees_id'    => $request->fees_id,
                'student_id' => $request->student_id
            ])->first();

            // If Fees Paid Doesn't Exists
            if (empty($feesPaid)) {
                $feesPaidResult = $this->feesPaid->create([
                    'date'                => date('Y-m-d', strtotime($request->date)),
                    'is_fully_paid'       => 0,
                    'is_used_installment' => 0,
                    'fees_id'             => $request->fees_id,
                    'student_id'          => $request->student_id,
                    'amount'              => $request->total_amount,
                ]);
            } else {
                $feesPaidResult = $this->feesPaid->update($feesPaid->id, [
                    'amount' => $request->total_amount + $feesPaid->amount
                ]);
            }


            $optionalFeesPaymentData = array();

            // Loop to the Optional Fees
            if (!empty($request->fees_class_type)) {
                foreach ($request->fees_class_type as $key => $feesClassType) {
                    if (isset($feesClassType['id'])) {
                        $optionalFeesPaymentData[] = array(
                            'student_id'    => $request->student_id,
                            'class_id'      => $request->class_id,
                            'fees_class_id' => $feesClassType['id'],
                            'mode'          => $request->mode,
                            'cheque_no'     => $request->mode == 2 ? $request->cheque_no : null,
                            'amount'        => $feesClassType['amount'],
                            'fees_paid_id'  => $feesPaidResult->id,
                            'date'          => date('Y-m-d', strtotime($request->date)),
                            'status'        => "Success",
                            'created_at'    => now(),
                            'updated_at'    => now()
                        );
                    }
                }
            }

            $this->optionalFee->createBulk($optionalFeesPaymentData);

            DB::commit();
            ResponseService::successResponse("Data Updated SuccessFully");
        } catch (Throwable $e) {
            DB::rollback();
            ResponseService::logErrorResponse($e, 'FeesController -> compulsoryFeesPaidStore method ');
            ResponseService::errorResponse();
        }
    }
    /* END : Fees Paid Module */
}
