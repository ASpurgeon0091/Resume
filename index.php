<?php

include("../vars.php");
include("../mysql.php");
include("../functions.php");
include("../auth.php");



if ($_GET[action]=="login") {


	$input_email	= mysql_escape_string($_POST['inputUsernameEmail']);
	$input_password	= mysql_escape_string($_POST['inputPassword']);

	$show_error=CheckData($input_email,$input_password);

}

function CheckData($user,$pass) {

	$result=mysql_query("SELECT * FROM `customer_accounts` WHERE email='$user'") 
		or die ("FAILED TO RETRIEVE USER DATA LOGIN.CHECKDATA WITH EMAIL:$user");
	$logindata=mysql_fetch_array($result);


	if ($logindata['password']==md5("r87h83j8jS".	$pass."FSAJDFDSE!@#2") && $logindata['password']!="") {

		$todays_date=date("m-d-y");
		mysql_query("UPDATE `customer_accounts` SET last_login='$todays_date' WHERE username='$user' LIMIT 1");

		session_start();
		//session_unset();
		//session_destroy();

		$sessionid=CreateSession($user,md5($pass."FGsasdf22"));

		//@ session_register("SessionID");
		//$HTTP_SESSION_VARS["SessionID"] = $sessionid;
		$_SESSION["SessionID"]=$sessionid;

		header("Location: /dashboard/");
		die();
	} else {
		if (mysql_num_rows($result)==0) {
			return "Sorry, that e-mail is not currently registered with YardBuddy.";
		} else {
			return "You have entered an invalid password.";
		}
		
	}

}


include("../header.php");

?>


  <div id="barba-wrapper">
    <div class="barba-container">
      
<div id="login-page">
  <div class="section-header">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h2>
            Welcome Back <br>
            <small>
              Sign in to your account
            </small>
          </h2>

        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="login-box">
      <div class="row">
        <div class="col-md-8">

<?php
if ($show_error!="") {
?>

                <div class="bs-component">
                  <div class="panel panel-danger">
                    <div class="panel-heading">
                      <h3 class="panel-title"><center><?php echo($show_error); ?></h3>
                    </div>
                  </div>

<?php
}
?>

          <form role="form" method="POST" action="?action=login">
            <div class="form-group">
              <label for="inputUsernameEmail">E-mail</label>
              <input type="text" class="form-control input-lg" placeholder="Please enter your e-mail" id="inputUsernameEmail" name="inputUsernameEmail" tabindex="1" value="<?php echo($input_email); ?>">
            </div>
            <div class="form-group">
              <a class="pull-right" href="#" tabindex="5">Forgot password?</a>
              <label for="inputPassword">Password</label>
              <input type="password" class="form-control input-lg" id="inputPassword" name="inputPassword" placeholder="Please enter your password" tabindex="2">
            </div>
            <div class="checkbox pull-right">
              <div>
                <input class="magic-checkbox" type="checkbox" name="layout" id="remember" checked="checked">
                <label for="remember" tabindex="4">Remember Me</label>
              </div>
            </div>

            <button type="submit" class="btn btn-lg btn-primary" tabindex="3">
              Log In
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>


    </div>
  </div>

  
<footer id="main-footer">
  <div class="container menu-container">
    <div class="row">
      <div class="col-sm-8 col-xs-12">
        <div class="row">
          <div class="col-sm-4 col-xs-6 footer-menu">
            <h3><span>Overview</span></h3>
            <ul class="unstyled">
              <li>
                <a href="/">
                  <i class="material-icons">home</i>
                  Home
                </a>
              </li>

              <li>
                <a href="#">
                  <i class="material-icons">border_color</i>
                  Request Service
                </a>
              </li>

              <li>
                <a href="#">
                  <i class="material-icons">supervisor_account</i>
                  Become a contractor
                </a>
              </li>
              <li>
                <a href="#">
                  <i class="material-icons">done_all</i>
                  How it works
                </a>
              </li>
              <li>
                <a href="#">
                  <i class="material-icons">monetization_on</i>
                  Pricing
                </a>
              </li>
              <li>
                <a href="#">
                  <i class="material-icons">contact_mail</i>
                  Contact
                </a>
              </li>

            </ul>
          </div><!-- footer menu -->

          <div class="col-sm-4 col-xs-6 footer-menu">
            <h3><span>Menu</span></h3>
            <ul class="unstyled">
              <li>
                <a href="#">
                  <i class="material-icons">brightness_4</i>
                  Login - Request Service
                </a>
              </li>



              <li>
                <a href="#">
                  <i class="material-icons">record_voice_over</i>
                  Knowledge Base
                </a>
              </li>
              <li>
                <a href="#">
                  <i class="material-icons">question_answer</i>
                  FAQ
                </a>
              </li>
            </ul>
          </div><!-- footer menu -->

          <div class="col-sm-4 hidden-xs footer-menu">
            <h3><span>Social</span></h3>
            <ul class="unstyled">
              <li>
                <a href="#">
                  <i class="fa fa-youtube"></i>
                  Youtube
                </a>
              </li>
              <li>
                <a href="#">
                  <i class="fa fa-facebook"></i>
                  Facebook
                </a>
              </li>
              <li>
                <a href="#">
                  <i class="fa fa-twitter"></i>
                  Twitter
                </a>
              </li>
              <li>
                <a href="#">
                  <i class="fa fa-linkedin"></i>
                  LinkedIn
                </a>
              </li>
            </ul>
          </div>

        </div>
      </div>




    </div>
  </div>

  <div class="copyright-footer">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          YardBuddy. Copyright &copy; 2016-2017. All Rights Reserved

        </div>
      </div>
    </div>
  </div>
</footer>


<div class="modal fade zoom-out" id="purchaseModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Modal example</h4>
      </div>
      <div class="modal-body">
        <p class="lead">
          This is a modal with costom transitions. You can choose from 'zoom-out' 'move-horizontal' & 'newspaper-effect'.
        </p>

        <p>
          Below are some are some other home page variants that you can use -
        </p>

        <ul>
          <li>
            <a href="#">
              Default home page with 2
            </a>
          </li>
          <li>
            <a href="#">
              Home page for software products
            </a>
          </li>
          <li>
            <a href="#">
              Home page for Mobile apps
            </a>
          </li>
        </ul>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->




   

  <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>

  <!-- build:js scripts/vendor.js -->
    <script src="/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="/plugins/slick/slick.min.js"></script>
    <script src="/plugins/countUp/dist/countUp.js"></script>
    <script src="/plugins/lightbox/dist/ekko-lightbox.min.js"></script>
    <script src="/plugins/isotope/isotope.pkgd.js"></script>
    <script src="/plugins/barba/dist/barba.min.js"></script>
    <script src="/plugins/aos/aos.js"></script>
  <!-- endbuild -->

  <script type="text/javascript" src="js/main.js"></script>



   
</body>
</html>
