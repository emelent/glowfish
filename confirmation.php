<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title></title>
</head>
<body>
  <form action="https://sandbox.payfast.co.za/eng/process" method="POST">
    <!-- Merchant Details -->
    <input type="hidden" name="merchant_id" value="10003805"/>
    <input type="hidden" name="merchant_key" value="e1jkl1rv70689"/>
    <input type="hidden" name="return_url" value="https://glowfish.herokuapp.com/return.php"/>
    <input type="hidden" name="cancel_url" value="https://glowfish.herokuapp.com/cancel.php"/>
    <input type="hidden" name="notify_url" value="https://glowfish.herokuapp.com/notify.php"/>
    
    <!-- Buyer Details -->
    <input type="input" name="name_first" placeholder="First Name"/><br/>
    <input type="input" name="name_last" placeholder="Last Name"/><br/>
    <input type="email" name="email_address" placeholder="Email Address"/><br/>
    
    <!-- Transaction Details -->
    <input type="hidden" name="m_payment_id" value="youPAIDbruh">
    <input type="hidden" name="amount" value="300">
    <input type="input" name="item_name" value="Magic Sandwich"><br/>
    
    <button>I'm Ready To Pay</button>
  </form>
</body>
</html>
