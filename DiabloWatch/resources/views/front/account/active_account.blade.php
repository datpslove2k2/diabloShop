<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Enter your description here"/>
    <title>Notification By Diablo Watch</title>
</head>
<body>
    <div style="text-align: center">

        <h2>Hello {{$user->name}}</h2>
        <h3>To continue using the service, please click the 'Verify Account' button to activate your account</h3>
        <a href="{{route('account.actived', ['user' => $user->id, 'token' => $user->token])}}" style="display:inline-block; background: green; color: #fff; padding: 7px 25px; font-weight:bold">
    
        Verify Account</a>
    </div>
</body>


</html>
