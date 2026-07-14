<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ config('app.name','Teule Kenya') }}
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #000096;
            --danger: #ff0e3b;
            --yellow: #ffde00;
        }


        body {
    font-family: 'Segoe UI', sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

        .navbar {
            padding: 20px 0;
        }


        .logo {
            height: 55px;
        }


        .login-btn {
            background: var(--primary);
            color: white;
            border-radius: 30px;
            padding: 12px 30px;
            border: none;
        }


        .login-btn:hover {
            background: #000066;
            color: white;
        }


        .hero {
            background: linear-gradient(120deg,
                    #f5f7ff,
                    white);
            padding: 80px 0;
            flex: 1;
    display: flex;
    align-items: center;

        }


        .hero h1 {
            font-size: 55px;
            font-weight: 800;
            color: var(--primary);
        }


        .hero span {
            color: var(--danger);
        }


        .hero p {
            font-size: 20px;
            color: #555;
        }


        .hero-img {
            width: 100%;
            max-width: 520px;
        }


        .feature-card {

            border: none;
            padding: 35px;
            border-radius: 20px;
            box-shadow:
                0 10px 30px rgba(0, 0, 0, .08);

            height: 100%;
        }


        .feature-icon {

            width: 60px;
            height: 60px;

            background: #eef0ff;

            color: var(--primary);

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 50%;

            font-size: 25px;

            margin-bottom: 20px;

        }


        .stats {

            background: var(--primary);

            color: white;

            padding: 40px 0;

        }


      footer {

    background: #111;

    color: white;

    padding: 20px;

    margin-top: auto;

}

    </style>

</head>


<body>

    <section class="hero">

        <div class="container">

            <div class="row align-items-center">


                <div class="col-lg-6">


                    <h1>
                        Smart School
                        <span>
                            Management
                        </span>
                        System
                    </h1>


                    <p>
                        Manage students, fees, attendance,
                        examinations and communication
                        from one powerful platform.
                    </p>


                    <a href="{{ route('login') }}" class="btn login-btn btn-lg mt-3">

                        Login To Dashboard

                    </a>


                </div>



                <div class="col-lg-6 text-center">

                    <img src="{{ asset('assets/landing_page_images/heroImg.png') }}" class="hero-img">


                </div>


            </div>

        </div>

    </section>



    <footer class="text-center">

        © {{date('Y')}} Teule Kenya.
        All Rights Reserved.

    </footer>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>