<?php 
	//подключаемся к БД
    $host = 'localhost';
    $db   = 'images';
    $user = 'root';
    $pass = '';
    $charset = 'utf8';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    $connection = new PDO($dsn, $user, $pass)  or die ("Ошибка " . mysqli_error($connection));

    //обрабатываем полученый файл
    $out = '';
    if (isset($_POST['submit'])) {
    	$fileName = $_FILES['file']['name'];
    	$fileTmpName = $_FILES['file']['tmp_name'];
    	$fileType = $_FILES['file']['type'];
    	$fileError = $_FILES['file']['error'];
    	$fileSize = $_FILES['file']['size'];


    	$fileExtension = strtolower(end(explode('.', $fileName)));//вынимаем разрешение файл, то что после точки
    	$fileName = explode('.', $fileName)[0];// имя файла
    	$fileName = preg_replace('/[0-9]/', '', $fileName); //убираем цифры из имени файла
    	$allowedExtensions = ['jpg', 'jpeg', 'png']; // допустимы разрешения файла

    	if (in_array($fileExtension, $allowedExtensions))// проверяем разрешение файла
    	{	
    		if ($fileSize < 5000000)// проверяем размер файла
    		{	
    			if ($fileError === 0)// проверяем есть ли ошибки
    			{
    				// записываем файл в бд
    				$connection->query("INSERT INTO images (`imgname`, `extension`) VALUES ('$fileName', '$fileExtension');");
    				// получаем id последнего загруженого файла
    				$lastID = $connection->query("SELECT MAX(id) FROM `images`");
    				$lastID = $lastID->fetchAll();
    				$lastID = $lastID[0][0];

    				// создаем новое имя файлу добавляя id в начало
    				$fileNameNew = $lastID . $fileName . '.' . $fileExtension;
    				$fileDestination = 'uploads/' . $fileNameNew; // адрес папки с файлом
    				move_uploaded_file($fileTmpName, $fileDestination); // перемещаем файл из tmp в созадную папку
    				$out = "успех";
    			}
    			else
    			{
    				$out = "Произошла ошибка";
    			}
    		
    		}
    		else
    		{
    			$out = "Слишком большой размер файла";
    		}
    		
    	}
    	else
    	{
    		$out = "Неверный тип расширения файла";
    	}
    }

    //получаем все картинки из БД 
    $date = $connection->query("SELECT * FROM `images`");



// echo "<pre>";
// var_dump(isset($_POST['submit']));
// echo "</pre>";
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>php galery</title>
</head>
<style>
	.wrap {
		width: 1024px;
		margin: 40px auto 0;
	}
	.imgWrap {
		display: -webkit-flex;
		display: -moz-flex;
		display: -ms-flex;
		display: -o-flex;
		display: flex;
		justify-content: space-between;
		flex-wrap: wrap;
	}
	.imgWrap div {
		margin: 10px 0;
	}
</style>
<body>
	<div class="wrap">
		<h2>Галерея картинок на PHP</h2>
		<div class="imgWrap">
			<?php foreach ($date as $img) { 

				$delete = "delete".$img['id'];// Заносим id в переменную для удаления из бд
				$image = "uploads/" . $img['id'] . $img['imgname'] . '.' . $img['extension'];// пишем путь картинки в переменную
				if (isset($_POST[$delete])) { // проверяем значение пост
					$imageId = $img['id'];
					$connection->query("DELETE FROM `images` WHERE id='$imageId'");// и удаляем картинку с этим id из бд 
					if (file_exists($image)) {
						unlink($image); // а потом и из папки где она лижит
					}
				}

				
				if (file_exists($image)) { // выводим картинки в цикле
					echo "<div>
					<img width='150' height='auto' src=$image>
					<form method='post'><button name = 'delete".$img['id']."'>delete</button>
					</form>
					</div>";
				}
				
			} ?>
		</div>
		<form method="post" enctype="multipart/form-data">
			<p><?= $out ?></p>
			<input type="file" name="file" required>
			<input type="submit" name="submit">
		</form>
	</div>

	
</body>
</html>