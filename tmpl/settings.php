<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<h2>Daashub Settings</h2>
<?php if(isset($_POST['daashub_token'])):?>
<p class="daashub_token_success">
    The token has been saved!
</p>
<?php endif ?>
<form action="" method="post" id="daashub_token_form">
    <p><label for="daashub_token">API Token</label></p>
    <p><input type="text" id="daashub_token" name="daashub_token" value="<?php echo get_option('daashub_token') ?>" /></p>
    <p><input type="submit" class="button button-primary" value="Save" /></p>
</form>

<style>
    #daashub_token_form input[type="text"]
    {
        width: 100%;
        max-width: 500px;
        box-sizing: border-box;
    }    
    
    .daashub_token_success
    {
        box-sizing: border-box;
        padding: 5px;
        width: 100%;
        max-width: 500px;
        border: 1px solid #cdc;
        font-weight: bold;
        background: #efe;
    }
    
</style>