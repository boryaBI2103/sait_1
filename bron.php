<?php
$message = '';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Diplom";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        empty($_POST['first_name']) ||
        empty($_POST['last_name']) ||
        empty($_POST['phone']) ||
        empty($_POST['guests']) ||
        empty($_POST['table_type']) ||
        empty($_POST['reservation_time']) ||
        empty($_POST['reservation_date'])
    ) {
        $message = "<div class='alert alert-error'>Заполните все обязательные поля.</div>";
    } else {

        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $guests = (int) $_POST['guests'];
        $table_type = $conn->real_escape_string($_POST['table_type']);
        $reservation_time = $conn->real_escape_string($_POST['reservation_time']);
        $reservation_date = $conn->real_escape_string($_POST['reservation_date']);

        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE reservation_time = ? AND reservation_date = ?");
        $stmt->bind_param("ss", $reservation_time, $reservation_date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result['count'] >= 15) {
            $message = "<div class='alert alert-error'>Все столики забронированы на это время и дату.</div>";
        } else {

            $stmt = $conn->prepare("INSERT INTO reservations (first_name, last_name, phone, guests, table_type, reservation_time, reservation_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssissss", $first_name, $last_name, $phone, $guests, $table_type, $reservation_time, $reservation_date);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Бронирование успешно оформлено!</div>";
            } else {
                $message = "<div class='alert alert-error'>Ошибка при бронировании: " . htmlspecialchars($conn->error) . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/style.css">
    <title>BarBoriski</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .alert {
            margin-top: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
            max-width: 100%;
        }

        .alert-success {
            background-color: #e6f9ed;
            color: #207a3c;
            border: 1px solid #b0e2c3;
        }

        .alert-error {
            background-color: #fde8e8;
            color: #b30000;
            border: 1px solid #f5bcbc;
        }

        h1 {
            text-align: center;
            margin-top: 40px;
            font-size: 2.2rem;
            color: #222;
        }

        form {
            max-width: 500px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: 0.3s ease;
        }

        form label {
            display: block;
            margin-bottom: 15px;
            font-weight: 600;
            color: #444;
        }

        form input[type="text"],
        form input[type="number"],
        form select {
            width: 100%;
            padding: 12px 16px;
            border: none;
            border-radius: 12px;
            background: #f0f0f3;
            box-shadow: inset 4px 4px 8px #d1d9e6, inset -4px -4px 8px #ffffff;
            font-size: 1rem;
            transition: 0.2s ease-in-out;
        }

        form input:focus,
        form select:focus {
            outline: none;
            background: #e8ebf0;
            box-shadow: inset 2px 2px 4px #cbd3e1, inset -2px -2px 4px #ffffff;
        }

        form button {
            width: 100%;
            padding: 14px;
            background: #ff3f3a;
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(255, 63, 58, 0.3);
            transition: 0.3s ease;
        }

        form button:hover {
            background: #e03732;
            box-shadow: 0 6px 18px rgba(255, 63, 58, 0.4);
        }

        @media (max-width: 600px) {
            form {
                margin: 20px;
                padding: 20px;
            }

            h1 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>

<body id="body">
    <div class="wrapper">
        <div class="overlay"></div>
        <header class="header">

            <div class="header__top">
                <div class="container">
                    <div class="header__top-inner">
                        <a class="header__logo logo" href="/">
                            <img src="images/logo.png" alt="Logo">
                        </a>
                        <nav class="header__nav">

                        </nav>
                        <div class="header__btn-box">
                            <a href="index.html" class="header__top-btn button">Главная</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header__body">
                <div class="container">
                    <div class="header__body-inner">
                        <div class="header__main">
                            <div class="header__content">
                                <a class="header__play" href="images/video.mp4" data-fancybox>
                                    <svg width="52" height="52" viewBox="0 0 52 52" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="26" cy="26" r="26" fill="#FF3F3A" />
                                        <path
                                            d="M32.5 25.134C33.1667 25.5189 33.1667 26.4811 32.5 26.866L23.5 32.0622C22.8333 32.4471 22 31.966 22 31.1962L22 20.8038C22 20.034 22.8333 19.5529 23.5 19.9378L32.5 25.134Z"
                                            fill="white" />
                                    </svg>
                                    Знакомство с нами
                                </a>
                                <h1 class="header__title">
                                    Отдохни вместе с "BarBoriski"
                                </h1>
                            </div>
                            <img class="header__img" src="images/hero-imaget.png" alt="">
                        </div>
                        <ul class="header__row">
                            <li class="header__row-item">
                                <span>15</span>
                                Сотрудников
                            </li>
                            <span></span>
                            <li class="header__row-item">
                                <span>23</span>
                                Столика
                            </li>
                            <span></span>
                            <li class="header__row-item">
                                <span>1</span>
                                Зал
                            </li>
                            <span></span>
                            <li class="header__row-item">
                                <span>1</span>
                                Барная стойка
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
        <h1>Форма бронирования столика</h1>

        <form method="post" action="">
            <?php if (!empty($message))
                echo $message; ?>

            <label>Имя:
                <input type="text" name="first_name" required />
            </label>

            <label>Фамилия:
                <input type="text" name="last_name" required />
            </label>

            <label>Телефон:
                <input type="text" name="phone" required />
            </label>

            <label>Количество персон:
                <input type="number" name="guests" min="1" max="15" required />
            </label>

            <label>Тип столика:
                <select name="table_type" required>
                    <option value="bar">Барная стойка</option>
                    <option value="hall">Зал</option>
                </select>
            </label>

            <label>Дата бронирования:
                <input type="date" name="reservation_date" required />
            </label>

            <label>Время бронирования:
                <select name="reservation_time" required>
                    <?php
                    for ($hour = 9; $hour < 23; $hour += 2) {
                        $time = date("Y-m-d") . " " . str_pad($hour, 2, '0', STR_PAD_LEFT) . ":00:00";
                        echo "<option value='$time'>" . date("H:i", strtotime($time)) . "</option>";
                    }
                    ?>
                </select>
            </label>

            <button type="submit">Забронировать</button>
        </form>
        <footer class="footer">
            <div class="footer__top">
                <div class="container">
                    <div class="footer__top-inner">
                        <div class="footer__top-col">
                            <a class="footer__logo logo" href="#">
                                <img src="images/logo.png" alt="logo">
                            </a>
                            <div class="footer__top-text">
                                "Barboriski — место, где уют встречается с изыском. Авторские коктейли, живая музыка и
                                теплая атмосфера создают идеальный уголок для отдыха. Здесь каждый вечер становится
                                особенным!"
                            </div>
                            <ul class="footer__socials">

                                <li class="footer__socials-item">
                                    <a class="footer__socials-link" href="#">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M21.3903 4.11358C22.4182 4.39044 23.2288 5.20086 23.5055 6.22899C24.0197 8.10691 23.9999 12.0213 23.9999 12.0213C23.9999 12.0213 23.9999 15.9158 23.5057 17.7939C23.2288 18.8218 22.4184 19.6324 21.3903 19.9091C19.5122 20.4035 12 20.4035 12 20.4035C12 20.4035 4.50731 20.4035 2.60961 19.8895C1.58148 19.6127 0.771054 18.802 0.4942 17.7741C0 15.9158 0 12.0015 0 12.0015C0 12.0015 0 8.10691 0.4942 6.22899C0.770871 5.20104 1.60125 4.37066 2.60943 4.09399C4.48753 3.59961 11.9998 3.59961 11.9998 3.59961C11.9998 3.59961 19.5122 3.59961 21.3903 4.11358ZM15.8549 12.0016L9.60788 15.5996V8.40355L15.8549 12.0016Z"
                                                fill="#787A80" />
                                        </svg>
                                    </a>
                                </li>
                                <li class="footer__socials-item">
                                    <a class="footer__socials-link" href="#">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M9.63262 15.1813L9.2687 20.7653C9.78938 20.7653 10.0149 20.5213 10.2853 20.2283L12.7264 17.6833L17.7847 21.7243C18.7124 22.2883 19.366 21.9913 19.6163 20.7933L22.9365 3.8214L22.9374 3.8204C23.2317 2.3244 22.4415 1.73941 21.5377 2.1064L2.02135 10.2574C0.689406 10.8214 0.709573 11.6314 1.79493 11.9984L6.78447 13.6913L18.3742 5.78039C18.9196 5.38639 19.4155 5.60439 19.0076 5.99839L9.63262 15.1813Z"
                                                fill="#787A80" />
                                        </svg>
                                    </a>
                                </li>
                                <li class="footer__socials-item">
                                    <a class="footer__socials-link" href="#">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M21.9804 7.88005C21.9336 6.81738 21.7617 6.0868 21.5156 5.45374C21.2616 4.78176 20.8709 4.18014 20.359 3.68002C19.8589 3.1721 19.2533 2.77743 18.5891 2.52745C17.9524 2.28127 17.2256 2.10943 16.163 2.06257C15.0923 2.01175 14.7525 2 12.0371 2C9.32172 2 8.98185 2.01175 7.9152 2.05861C6.85253 2.10546 6.12195 2.27746 5.48904 2.52348C4.81692 2.77743 4.2153 3.16814 3.71517 3.68002C3.20726 4.18014 2.81274 4.78573 2.5626 5.44992C2.31643 6.0868 2.14458 6.81341 2.09773 7.87609C2.04691 8.9467 2.03516 9.28658 2.03516 12.002C2.03516 14.7173 2.04691 15.0572 2.09376 16.1239C2.14061 17.1865 2.31261 17.9171 2.55879 18.5502C2.81274 19.2221 3.20726 19.8238 3.71517 20.3239C4.2153 20.8318 4.82088 21.2265 5.48507 21.4765C6.12195 21.7226 6.84856 21.8945 7.91139 21.9413C8.97788 21.9883 9.31791 21.9999 12.0333 21.9999C14.7486 21.9999 15.0885 21.9883 16.1552 21.9413C17.2178 21.8945 17.9484 21.7226 18.5813 21.4765C19.9254 20.9568 20.9881 19.8941 21.5078 18.5502C21.7538 17.9133 21.9258 17.1865 21.9726 16.1239C22.0195 15.0572 22.0312 14.7173 22.0312 12.002C22.0312 9.28658 22.0273 8.9467 21.9804 7.88005ZM20.1794 16.0457C20.1364 17.0225 19.9723 17.5499 19.8355 17.9015C19.4995 18.7728 18.808 19.4643 17.9367 19.8004C17.585 19.9372 17.0538 20.1012 16.0808 20.1441C15.026 20.1911 14.7096 20.2027 12.0411 20.2027C9.37255 20.2027 9.0522 20.1911 8.00113 20.1441C7.02437 20.1012 6.49693 19.9372 6.1453 19.8004C5.71171 19.6402 5.31704 19.3862 4.9967 19.0541C4.6646 18.7298 4.41065 18.3391 4.2504 17.9055C4.11365 17.5539 3.94959 17.0225 3.9067 16.0497C3.8597 14.9948 3.8481 14.6783 3.8481 12.0097C3.8481 9.34122 3.8597 9.02087 3.9067 7.96995C3.94959 6.99319 4.11365 6.46575 4.2504 6.11412C4.41065 5.68038 4.6646 5.28586 5.00067 4.96536C5.32483 4.63327 5.71553 4.37931 6.14927 4.21921C6.5009 4.08247 7.03231 3.9184 8.00509 3.87537C9.05999 3.82851 9.37652 3.81676 12.0449 3.81676C14.7174 3.81676 15.0337 3.82851 16.0848 3.87537C17.0616 3.9184 17.589 4.08247 17.9406 4.21921C18.3742 4.37931 18.7689 4.63327 19.0892 4.96536C19.4213 5.28967 19.6753 5.68038 19.8355 6.11412C19.9723 6.46575 20.1364 6.99701 20.1794 7.96995C20.2262 9.02484 20.238 9.34122 20.238 12.0097C20.238 14.6783 20.2262 14.9908 20.1794 16.0457Z"
                                                fill="#787A80" />
                                            <path
                                                d="M12.0371 6.86423C9.20069 6.86423 6.89937 9.1654 6.89937 12.002C6.89937 14.8385 9.20069 17.1397 12.0371 17.1397C14.8736 17.1397 17.1748 14.8385 17.1748 12.002C17.1748 9.1654 14.8736 6.86423 12.0371 6.86423ZM12.0371 15.3347C10.197 15.3347 8.70438 13.8422 8.70438 12.002C8.70438 10.1617 10.197 8.66924 12.0371 8.66924C13.8774 8.66924 15.3698 10.1617 15.3698 12.002C15.3698 13.8422 13.8774 15.3347 12.0371 15.3347Z"
                                                fill="#787A80" />
                                            <path
                                                d="M18.5776 6.6611C18.5776 7.32346 18.0405 7.86053 17.378 7.86053C16.7156 7.86053 16.1785 7.32346 16.1785 6.6611C16.1785 5.99858 16.7156 5.46167 17.378 5.46167C18.0405 5.46167 18.5776 5.99858 18.5776 6.6611Z"
                                                fill="#787A80" />
                                        </svg>
                                    </a>
                                </li>

                            </ul>
                        </div>
                        <div class="footer__top-col">
                            <h3 class="footer__top-title footer__top-title--slide">Навигация</h3>
                            <ul class="footer__top-list">
                                <li class="footer__top-item">
                                    <a class="footer__item-link" href="index.html">Главная</a>
                                </li>
                            </ul>
                        </div>
                        <div class="footer__top-col">
                            <h3 class="footer__top-title footer__top-title--slide">Свяжитесь с нами</h3>
                            <ul class="footer__top-list">
                                <li class="footer__top-item">
                                    <a class="footer__item-link" href="tel:4055550128">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M5.66683 1.94857C5.11454 1.94857 4.66683 2.39628 4.66683 2.94857V13.0527C4.66683 13.605 5.11454 14.0527 5.66683 14.0527H10.3335C10.8858 14.0527 11.3335 13.605 11.3335 13.0527V2.94857C11.3335 2.39628 10.8858 1.94857 10.3335 1.94857H10.2779L10.1155 2.3382C10.012 2.58661 9.76928 2.74842 9.50016 2.74842H6.50016C6.23105 2.74842 5.98832 2.58661 5.8848 2.3382L5.72242 1.94857H5.66683ZM3.3335 2.94857C3.3335 1.6599 4.37817 0.615234 5.66683 0.615234H10.3335C11.6222 0.615234 12.6668 1.6599 12.6668 2.94857V13.0527C12.6668 14.3414 11.6222 15.3861 10.3335 15.3861H5.66683C4.37816 15.3861 3.3335 14.3414 3.3335 13.0527V2.94857Z"
                                                fill="white" />
                                        </svg>
                                        +7-(888) 777-66-55
                                    </a>
                                </li>
                                <li class="footer__top-item">
                                    <a class="footer__item-link" href="mailto:hello@createx.com">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M15.1668 11.9993V4.66602C15.1668 3.56145 14.2714 2.66602 13.1668 2.66602L2.83349 2.66603C1.72893 2.66603 0.833496 3.56146 0.833496 4.66603V11.9993C0.833496 13.1039 1.72893 13.9993 2.8335 13.9993L13.1668 13.9993C14.2714 13.9993 15.1668 13.1039 15.1668 11.9993ZM13.8335 6.17232V11.9993C13.8335 12.3675 13.535 12.666 13.1668 12.666L2.8335 12.666C2.46531 12.666 2.16683 12.3675 2.16683 11.9993L2.16683 6.17223L6.87561 9.3742C7.55433 9.83573 8.44613 9.83573 9.12485 9.3742L13.8335 6.17232ZM13.8259 4.56509C13.7773 4.24479 13.5007 3.99935 13.1668 3.99935L2.83349 3.99936C2.49965 3.99936 2.22311 4.24476 2.17443 4.56501L7.62535 8.27164C7.85159 8.42548 8.14886 8.42548 8.3751 8.27164L13.8259 4.56509Z"
                                                fill="white" />
                                        </svg>
                                        barboriski@yandex.ru
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer__bottom">
                <div class="container">
                    <div class="footer__bottom-inner">
                        <div class="footer__copy">
                            © Все права защищены. Сделано с
                            <span></span>
                            by Boris Martirosyan
                        </div>
                        <a class="footer__go-top" href="#body">К началу</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>


</html>