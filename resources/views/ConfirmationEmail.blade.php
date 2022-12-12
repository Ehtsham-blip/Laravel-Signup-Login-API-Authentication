<!DOCTYPE html>
<html lang="en">
<head>
        <title>Registration Confirmation</title>
</head>
<body>
       <h1>Registration Confirmation Email</h1>

<p> <a href="{{ route('confirmation', ['code'=> $code, 'email' => $email])}}">Click Here</a></p>

</body>
</html>