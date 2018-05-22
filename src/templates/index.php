<? require 'header.php' ?>

<p>Welcome to Slim Framework Login Logout</p>

<?if (!empty($user)):?>
<p> <?=$user?>
<?endif;?>
<form action="/logout" method="GET">
 <p><input type="submit" value="Logout" />
</form>

<? require 'footer.php' ?>

