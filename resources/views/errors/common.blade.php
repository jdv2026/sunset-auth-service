<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>{{ $title }}</title>
		<style>
			body {
				font-family: Arial, sans-serif;
				background: #f2f2f2;
				color: #333;
				display: flex;
				justify-content: center;
				align-items: center;
				height: 100vh;
				margin: 0;
			}
			.error-container {
				text-align: center;
				background: #fff;
				padding: 40px 60px;
				border-radius: 8px;
				box-shadow: 0 8px 20px rgba(0,0,0,0.1);
			}
			h1 {
				font-size: 48px;
				margin-bottom: 20px;
				color: #e74c3c;
			}
			p {
				font-size: 18px;
				margin-bottom: 30px;
			}
			a {
				text-decoration: none;
				color: #3498db;
				font-weight: bold;
				border: 1px solid #3498db;
				padding: 10px 20px;
				border-radius: 4px;
				transition: all 0.3s ease;
			}
			a:hover {
				background: #3498db;
				color: #fff;
			}
		</style>
	</head>
	<body>
		<div class="error-container">
			<h1>{{ $title }}</h1>
			<p>{{ $description }}</p>
		</div>
	</body>
</html>
