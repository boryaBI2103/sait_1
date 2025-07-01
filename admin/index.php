<?php
session_start();

$correct_password = '25Борис';

if (!isset($_SESSION['authenticated'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $correct_password) {
            $_SESSION['authenticated'] = true;
            header("Location: " . $_SERVER['PHP_SELF']); 
            exit;
        } else {
            $error = "Неверный пароль!";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="ru">

    <head>
        <meta charset="UTF-8">
        <title>Вход</title>
        <style>
            body {
                font-family: Arial;
                background: #f2f2f2;
                text-align: center;
                margin-top: 100px;
            }

            input {
                padding: 8px;
                font-size: 16px;
            }

            button {
                padding: 8px 16px;
                font-size: 16px;
                background: #4CAF50;
                color: white;
                border: none;
            }

            .error {
                color: red;
                margin-top: 10px;
            }
        </style>
    </head>

    <body>
        <h2>Введите пароль для доступа</h2>
        <form method="POST">
            <input type="password" name="password" placeholder="Пароль" />
            <button type="submit">Войти</button>
        </form>
        <?php if (isset($error))
            echo "<div class='error'>$error</div>"; ?>
    </body>

    </html>
    <?php
    exit;
}
?>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Diplom";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$tables = [
    'positive_feedback' => 'id',
    'negative_feedback' => 'id',
    'suggestions' => 'id',
    'menu_items' => 'id',
    'Сотрудники' => 'ID_сотрудника',
    'Отдел' => 'ID_отдела'
];

function hasTimestampField($conn, $table)
{
    $result = $conn->query("SHOW COLUMNS FROM `$table`");
    while ($row = $result->fetch_assoc()) {
        if (stripos($row['Type'], 'timestamp') !== false || stripos($row['Type'], 'datetime') !== false) {
            return $row['Field'];
        }
    }
    return false;
}

foreach ($tables as $table => $id_field) {
    if (isset($_POST["delete_$table"])) {
        $id = (int) $_POST["id"];
        $conn->query("DELETE FROM `$table` WHERE `$id_field` = $id");
    }

    if (isset($_POST["update_$table"])) {
        $fields = $_POST[$table];
        $id = (int) $_POST["id"];
        $updates = [];
        foreach ($fields as $key => $val) {
            $val = $conn->real_escape_string($val);
            $updates[] = "`$key` = '$val'";
        }
        $sql = "UPDATE `$table` SET " . implode(", ", $updates) . " WHERE `$id_field` = $id";
        $conn->query($sql);
    }

    if (isset($_POST["add_$table"])) {
        $fields = $_POST[$table];
        $timestampField = hasTimestampField($conn, $table);
        if ($timestampField && isset($fields[$timestampField])) {
            unset($fields[$timestampField]);
        }

        $columns = implode(",", array_map(function ($k) {
            return "`$k`";
        }, array_keys($fields)));

        $values = implode(",", array_map(function ($v) use ($conn) {
            return "'" . $conn->real_escape_string($v) . "'";
        }, array_values($fields)));


        $sql = "INSERT INTO `$table` ($columns) VALUES ($values)";
        if (!$conn->query($sql)) {
            echo "Ошибка добавления в $table: " . $conn->error;
        }
    }
}

if (isset($_POST['add_feedback'])) {
    $comment = $conn->real_escape_string($_POST['Комментарий']);
    $id = (int) $_POST['ID_отзыва'];
    $sql = "INSERT INTO `Обратный_отзыв` (`ID_отзыва`, `Комментарий`, `ID_сотрудника`, `ID_отдела`, `ID_негативного_отзыва`, `ID_позитивного_отзыва`, `ID_предложения`) VALUES (
        $id,
    
        '$comment',
        " . (int) $_POST['ID_сотрудника'] . ",
        " . (int) $_POST['ID_отдела'] . ",
        " . (int) $_POST['ID_негативного_отзыва'] . ",
        " . (int) $_POST['ID_позитивного_отзыва'] . ",
        " . (int) $_POST['ID_предложения'] . "
    )";
    if (!$conn->query($sql)) {
        echo "Ошибка при добавлении отзыва: " . $conn->error;
    }
}

if (isset($_POST['update_feedback'])) {
    $id = (int) $_POST['ID_отзыва'];
    $comment = $conn->real_escape_string($_POST['Комментарий']);
    $sql = "UPDATE `Обратный_отзыв` SET 
        `Комментарий` = '$comment',
        `ID_сотрудника` = " . (int) $_POST['ID_сотрудника'] . ",
        `ID_отдела` = " . (int) $_POST['ID_отдела'] . ",
        `ID_негативного_отзыва` = " . (int) $_POST['ID_негативного_отзыва'] . ",
        `ID_позитивного_отзыва` = " . (int) $_POST['ID_позитивного_отзыва'] . ",
        `ID_предложения` = " . (int) $_POST['ID_предложения'] . "
        WHERE `ID_отзыва` = $id";
    $conn->query($sql);
}

if (isset($_POST['delete_feedback'])) {
    $id = (int) $_POST['ID_отзыва'];
    $conn->query("DELETE FROM `Обратный_отзыв` WHERE `ID_отзыва` = $id");
}

function getOptions($conn, $table, $id, $name)
{
    $res = $conn->query("SELECT `$id`, `$name` FROM `$table`");
    $options = "";
    while ($row = $res->fetch_assoc()) {
        $options .= "<option value='{$row[$id]}'>{$row[$name]}</option>";
    }
    return $options;
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Управление таблицами</title>
    <style>
        body {
            font-family: Arial;
            background: #f2f2f2;
            margin: 20px;
        }

        h1,
        h2 {
            color: #333;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 40px;
            background: #fff;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        input,
        select {
            width: 100%;
            padding: 4px;
        }

        button {
            padding: 5px 10px;
            margin: 2px;
            background: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #45a049;
        }

        .table-block {
            margin-bottom: 50px;
        }
    </style>
</head>

<body>

    <h1>Управление таблицами</h1>

    <?php
    function printTable($conn, $table, $id_field)
    {
        $result = $conn->query("SELECT * FROM `$table`");
        echo "<div class='table-block'><h2>$table</h2>";
        echo "<table><tr>";
        $columns = [];

        if ($result && $result->num_rows > 0) {
            $columns = array_keys($result->fetch_assoc());
            foreach ($columns as $col) {
                echo "<th>$col</th>";
            }
            echo "<th>Действия</th></tr>";
            $result->data_seek(0);

            while ($row = $result->fetch_assoc()) {
                echo "<form method='POST'><tr>";
                foreach ($columns as $col) {
                    $val = htmlspecialchars($row[$col]);
                    $readonly = '';
                    if (in_array(strtolower($col), ['created_at', 'timestamp', 'date', 'datetime'])) {
                        $readonly = 'readonly';
                    }
                    echo "<td><input name='{$table}[$col]' value='$val' $readonly /></td>";
                }
                echo "<td>
                <input type='hidden' name='id' value='{$row[$id_field]}' />
                <button type='submit' name='update_$table'>Редактировать</button>
                <button type='submit' name='delete_$table' onclick=\"return confirm('Удалить?')\">Удалить</button>
            </td></tr></form>";
            }
        }

        echo "<form method='POST'><tr>";
        foreach ($columns as $col) {
            if (in_array(strtolower($col), ['created_at', 'timestamp', 'date', 'datetime'])) {
                echo "<td>--</td>";
            } else {
                echo "<td><input name='{$table}[$col]' placeholder='$col' /></td>";
            }
        }
        echo "<td><button type='submit' name='add_$table'>Добавить</button></td></tr></form>";
        echo "</table></div>";
    }

    foreach ($tables as $table => $id_field) {
        printTable($conn, $table, $id_field);
    }
    ?>

    <div class="table-block">
        <h2>Обратный_отзыв</h2>
        <table>
            <tr>
                <th>ID_отзыва</th>
                <th>Комментарий</th>
                <th>Сотрудник</th>
                <th>Отдел</th>
                <th>ID негативного</th>
                <th>ID позитивного</th>
                <th>ID предложения</th>
                <th>Действия</th>
            </tr>
            <?php
            $res = $conn->query("SELECT * FROM `Обратный_отзыв`");
            while ($row = $res->fetch_assoc()) {
                echo "<form method='POST'><tr>";
                echo "<td><input name='ID_отзыва' value='{$row['ID_отзыва']}' readonly /></td>";
                echo "<td><input name='Комментарий' value=\"" . htmlspecialchars($row['Комментарий']) . "\" /></td>";
                echo "<td><input name='ID_сотрудника' value='{$row['ID_сотрудника']}' /></td>";
                echo "<td><input name='ID_отдела' value='{$row['ID_отдела']}' /></td>";
                echo "<td><input name='ID_негативного_отзыва' value='{$row['ID_негативного_отзыва']}' /></td>";
                echo "<td><input name='ID_позитивного_отзыва' value='{$row['ID_позитивного_отзыва']}' /></td>";
                echo "<td><input name='ID_предложения' value='{$row['ID_предложения']}' /></td>";
                echo "<td>
                <button name='update_feedback'>Обновить</button>
                <button name='delete_feedback' onclick=\"return confirm('Удалить?')\">Удалить</button>
            </td></tr></form>";
            }
            ?>
            <form method="POST">
                <tr>
                    <td><input name="ID_отзыва" /></td>
                    <td><input name="Комментарий" /></td>
                    <td><select
                            name="ID_сотрудника"><?= getOptions($conn, 'Сотрудники', 'ID_сотрудника', 'Должность') ?></select>
                    </td>
                    <td><select name="ID_отдела"><?= getOptions($conn, 'Отдел', 'ID_отдела', 'Название') ?></select>
                    </td>
                    <td><select
                            name="ID_негативного_отзыва"><?= getOptions($conn, 'negative_feedback', 'id', 'id') ?></select>
                    </td>
                    <td><select
                            name="ID_позитивного_отзыва"><?= getOptions($conn, 'positive_feedback', 'id', 'id') ?></select>
                    </td>
                    <td><select name="ID_предложения"><?= getOptions($conn, 'suggestions', 'id', 'id') ?></select></td>
                    <td><button name="add_feedback">Добавить</button></td>
                </tr>
            </form>
        </table>
    </div>

</body>

</html>