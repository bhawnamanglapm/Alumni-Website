<html>
<head>
<title>Register here</title>
<meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
	<div class="row">
		<div class="col-sm-4"></div>
		<div class="col-sm-4">
  <h2>Registration Form</h2><br><br>
  <form method="POST" action="two.php">
    <div class="form-group">
      <label for="roll">Roll Number:</label>
      <input type="text" class="form-control" id="roll" placeholder="Enter Roll number" name="roll" maxlength="32" required>
    </div>
    <div class="form-group">
      <label for="name">Name:</label>
      <input type="text" class="form-control" id="name" placeholder="Enter Name" name="name" maxlength="120" required>
    </div>
    <div class="form-group">
      <label for="email">Email:</label>
      <input type="email" class="form-control" id="email" placeholder="Enter Email" name="email" maxlength="254" required>
    </div>
    <div class="form-group">
      <label for="mob">Mobile:</label>
      <input type="tel" class="form-control" id="mob" placeholder="Enter Mobile Number" name="mob" maxlength="32" required>
    </div>
    <div class="form-group">
      <label for="address">Address:</label>
      <textarea class="form-control" id="address" placeholder="Enter Your Address" name="address" maxlength="1000" required></textarea>
      <center><button type="submit" class="btn btn-primary">Submit</button></center>
  </form></div>
<div class="col-sm-4"></div>
</div>
</div>
</form>
</body>
</html>
