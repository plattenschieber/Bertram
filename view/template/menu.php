<!-- Login -->
<nav style="position: absolute; top: 2px; right: 2px">

    <a class="btn btn-default show-on-click" data-target="#login-box">Login</a>


    <div id="login-box" class="close-on-mouseout hidden">
        <a class="hide-on-click right" data-target="#login-box"><span class="glyphicon glyphicon-remove"></span></a>
        <form id="frmLogin" name="frmLogin" action="<?= Func::path() ?>" method="post">
            <div class="field-block">
                <label for="email">E-Mail</label>
                <input type="email" id="lEmail"  class="form-control" name="lEmail" placeholder="E-Mail-Adresse" value="" />
                <p class="help-block hidden"></p>
            </div>

            <div class="field-block">
                <label for="pass">Passwort</label>
                <input type="password" id="lPass"  class="form-control" name="lPass" value="" />
                <p class="help-block hidden"></p>
            </div>

            <div class="field-block text-right" >
                <a id="reset-link" href="<?= HOME ?>/reset">Passwort vergessen?</a>
            </div>
            <p class="center" id="frmLogin-response"></p>


            <div class="button-box">
                <button class="btn btn-funilo std-ajax" data-url="/ajax/login" data-form="#frmLogin" data-reload="<?= Func::path() ?>" type="submit" id="lSubmit" name="lSubmit"><?= BTN_LOGIN ?></button>
            </div>  
        </form>
    </div>


</nav>