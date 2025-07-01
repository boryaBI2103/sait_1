<?php
$message = '';
$reservation_found = false;
$reservation_id = null;

// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Diplom";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Обработка поиска брони
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);

    // Поиск брони
    $stmt = $conn->prepare("SELECT id FROM reservations WHERE first_name = ? AND last_name = ?");
    if (!$stmt) {
        die("Ошибка prepare(): " . $conn->error);
    }

    $stmt->bind_param("ss", $first_name, $last_name);

    if (!$stmt->execute()) {
        die("Ошибка execute(): " . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $reservation_found = true;
        $reservation_id = $row['id'];
    } else {
        $message = "<div class='alert alert-error'>Бронь не найдена.</div>";
    }
}

// Обработка оформления заказа
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order'])) {
    $reservation_id = intval($_POST['reservation_id']);
    $dish_ids = $_POST['dishes'] ?? [];

    if ($reservation_id <= 0) {
        $message = "<div class='alert alert-error'>Ошибка: ID бронирования неверен.</div>";
    } elseif (empty($dish_ids)) {
        $message = "<div class='alert alert-error'>Выберите хотя бы одно блюдо.</div>";
    } else {
        foreach ($dish_ids as $dish_id) {
            $stmt = $conn->prepare("INSERT INTO orders (reservation_id, dish_id) VALUES (?, ?)");
            if (!$stmt) {
                die("Ошибка prepare(): " . $conn->error);
            }

            $stmt->bind_param("ii", $reservation_id, $dish_id);

            if (!$stmt->execute()) {
                $message .= "<div class='alert alert-error'>Ошибка при добавлении блюда ID {$dish_id}: " . htmlspecialchars($stmt->error) . "</div>";
            }
        }

        if (empty($message)) {
            $message = "<div class='alert alert-success'>Ваш предзаказ успешно оформлен!</div>";
        }
    }
}

// Получаем меню из базы
$menu = [];
$result = $conn->query("SELECT * FROM menu_items");
if ($result === false) {
    die("Ошибка запроса к таблице menu: " . $conn->error);
}
while ($row = $result->fetch_assoc()) {
    $menu[] = $row;
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
        /* === Форма поиска === */
        .search-form {
            max-width: 400px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            font-family: 'Segoe UI', sans-serif;
        }

        .search-form h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 22px;
            color: #333;
        }

        .search-form label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #555;
        }

        .search-form input[type="text"],
        .search-form input[type="number"],
        .search-form select {
            width: 100%;
            padding: 12px 16px;
            border: none;
            border-radius: 12px;
            background: #f5f5f7;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .search-form input:focus,
        .search-form select:focus {
            outline: none;
            background: #e9e9eb;
            box-shadow: 0 0 8px rgba(255, 63, 58, 0.2);
        }

        .search-form button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #ff3f3a;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .search-form button:hover {
            background-color: #e03732;
        }

        /* === Меню === */
        .menu {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .menu-category h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 15px;
            border-left: 5px solid #ff3f3a;
            padding-left: 10px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .menu-item:last-child {
            border-bottom: none;
        }

        .menu-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .menu-item div {
            flex-grow: 1;
            margin-left: 15px;
            font-size: 15px;
            color: #333;
        }

        /* === Заголовок "Предзаказ" === */
        h1 {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 2rem;
            /* ~32px */
            color: #222;
            text-align: center;
            margin-top: 60px;
            margin-bottom: 40px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* === Кнопка "Оформить предзаказ" === */
        button[type="submit"] {
            display: block;
            margin: 40px auto 0;
            padding: 12px 24px;
            background-color: #ff3f3a;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 6px 15px rgba(255, 63, 58, 0.3);
            transition: all 0.3s ease;
            width: fit-content;
        }

        button[type="submit"]:hover {
            background-color: #e03732;
            box-shadow: 0 8px 20px rgba(255, 63, 58, 0.5);
        }

        .menu-item button {
            background-color: #ff3f3a;
            color: white;
            border: none;
            padding: 8px 14px;
            font-size: 13px;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .menu-item button:hover {
            background-color: #e03732;
        }

        /* === Корзина (круглая кнопка) === */
        .btn-cart {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background-color: #ff3f3a;
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 12px;
            cursor: pointer;
            box-shadow: 0 6px 15px rgba(255, 63, 58, 0.3);
            z-index: 1000;
        }

        .btn-cart span {
            font-size: 12px;
        }

        /* === Всплывающая корзина === */
        .cart-popup {
            position: fixed;
            bottom: 70px;
            right: 20px;
            background: white;
            border-radius: 12px;
            padding: 15px;
            width: 280px;
            max-height: 300px;
            overflow-y: auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            display: none;
            z-index: 1000;
        }

        .cart-popup strong {
            display: block;
            margin-bottom: 10px;
            font-size: 15px;
            color: #333;
        }

        .cart-popup ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .cart-popup li {
            padding: 5px 0;
            font-size: 14px;
            color: #555;
        }

        /* === Сообщения об ошибках/успехе === */
        .alert {
            max-width: 400px;
            margin: 20px auto;
            padding: 15px 20px;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
        }

        .alert-success {
            background-color: #e6f9ed;
            color: #207a3c;
        }

        .alert-error {
            background-color: #fde8e8;
            color: #b30000;
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

        <h1>Предзаказ</h1>

        <?php if (!empty($message))
            echo $message; ?>

        <form method="post" action="" class="search-form">
            <h2>Предзаказ</h2>
            <label>Имя:
                <input type="text" name="first_name" required />
            </label>
            <label>Фамилия:
                <input type="text" name="last_name" required />
            </label>
            <button type="submit" name="search">Найти бронь</button>
        </form>

        <?php if ($reservation_found): ?>
            <div class="menu">
                <h2>Меню ресторана</h2>

                <?php
                $current_category = '';
                foreach ($menu as $item):
                    if ($current_category != $item['category']) {
                        if ($current_category != '')
                            echo '</div>';
                        echo "<div class='menu-category'><h3>" . htmlspecialchars($item['category']) . "</h3><div class='menu-items'>";
                        $current_category = $item['category'];
                    }
                    ?>
                    <div class="menu-item">
                        <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Блюдо" width="60" height="60">
                        <div><?= htmlspecialchars($item['description']) ?></div>
                        <button type="button"
                            onclick="addToOrder(<?= $item['id'] ?>, '<?= addslashes(htmlspecialchars($item['description'])) ?>')">
                            Добавить
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <form method="post" action="">
            <input type="hidden" name="reservation_id" value="<?= $reservation_id ?>">
            <div id="selected-inputs"></div>
            <button type="submit" name="order" style="margin: 20px auto; display: block;">Оформить предзаказ</button>
        </form>

        <!-- Кнопка корзины -->
        <button class="btn-cart" onclick="toggleCart()" title="Корзина"><span id="cart-count">0</span></button>
        <div class="cart-popup" id="cart-popup">
            <strong>Выбранные блюда:</strong>
            <ul id="cart-items"></ul>
        </div>

        <script>
            const cartItems = {};

            function addToOrder(id, name) {
                if (!cartItems[id]) {
                    cartItems[id] = { id: id, name: name, count: 1 };
                } else {
                    cartItems[id].count++;
                }
                updateCart();
            }

            function updateCart() {
                const itemsDiv = document.getElementById('selected-inputs');
                const list = document.getElementById('cart-items');
                const countEl = document.getElementById('cart-count');

                itemsDiv.innerHTML = '';
                list.innerHTML = '';
                let total = 0;

                for (let key in cartItems) {
                    const item = cartItems[key];
                    itemsDiv.innerHTML += `<input type="hidden" name="dishes[]" value="${item.id}">`;
                    list.innerHTML += `<li>${item.name} x${item.count}</li>`;
                    total += item.count;
                }

                countEl.innerText = total;
            }

            function toggleCart() {
                const popup = document.getElementById('cart-popup');
                popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
            }
        </script>
    <?php endif; ?>




</body>

</html>