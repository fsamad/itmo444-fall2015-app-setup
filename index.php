#!/usr/bin/env php

<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<title> HELLO </title>
</head>
<body>

<div id="form" class="container" style="border-style: solid; border-color: #003333; border-width:25px; width:500px; margin-left:450px">
<h1 style="color: maroon" align = 'center'> Form </h1>
<hr> 
<form enctype="multipart/form-data" action="result.php" method="POST">

<div class="form-group">
<label for="file" class="col-sm-2 control-label" > Send this file: </label>
<div class="col-sm-10">
<input type="file" class="form-control" id="file" name="file" />



<div class="form-group">
<label for="name" class="col-sm-2 control-label" > Your name: </label>
<div class="col-sm-10"> 
<input type="text" class="form-control" id="name" name="name" placeholder="User's name" />
</div> </div> 

<div class="form-group">
<label for="email" class="col-sm-2 control-label"> Your Email: </label>
<div class="col-sm-10">
<input type="email" class="form-control" id="email" name="email" placeholder="user's Email Address" />
</div> </div>

<div class="form-group">
<label for="phone" class="col-sm-2 control-label"> Your Telephone number </label>
<div class="col-sm-10">
<input type="text" class="form-control" id="phone" name="phone" placeholder="User's Phone number" />
</div> </div> 


<div class="form-group">
<div class="col-sm-10 col-sm-offser-2">
<input type="submit" id="submit" name="submit" value="Send File" class="btn btn-primary"/>
</br> </br>
</div> </div> 

</div>
</form>

<div id="form" class="container" style="border-style: solid; border-color: #003333; border-width:25px;width:500px; margin-left:450px">
<form enctype="mutlipart/form-data" action="gallery.php" method="POST">
<h2>Enter The User's Email for the gallery to browse:</h2> 
<hr>
<div class="form-group">
<label for="email" class="col-sm-2 control-label"> Your Email: </label>
<div class="col-sm-10">
<input type="text" class="form-control" id="email" name="email" placeholder="user's Email Address" />
</div> </div>

<div class="form-group">
<div class="col-sm-10 col-sm-offser-2">
<input type="submit" id="submit" name="submit" value="Load Gallery" class="btn btn-primary"/>
</br> </br>
</div> </div>

</form>
</body>
</html>


