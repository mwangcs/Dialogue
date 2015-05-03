<?php
  session_start();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo</title>

    <!-- Bootstrap core CSS -->
    <link href="lib/bootstrap.min.css" rel="stylesheet">
	 <link href="lib/style.css" rel="stylesheet">
	
	
  </head>

  <body>
	<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
      <form class="form-signin" action="DM.php?state=initial" method="POST">
        <h2 class="form-signin-heading">Dialogue Manager Demo</h2>
		<hr class="colorgraph">
    <p> <b> Dialogue System info: </b></p>
    <br>
    <p> <?php echo "System output: " . $_SESSION['message']; ?></p>
    <p> <?php echo $_SESSION['api_response']; ?> </p>
    <p> <?php echo "current dialogue state :" . $_SESSION['state']; ?></p>
    <hr>
    <br>
    <p> <b> From Wit.AI: </b> </p>
		<div class="form-group">
        <label for="intent" class="sr-only">JSON</label>
        <input type="intent" id="intent" name="intent" class="form-control input-lg" tabindex="3" placeholder="Intent" required autofocus>
		</div>
		<div class="form-group">
        <label for="entity" class="sr-only">Entity Type</label>
        <input type="entity" name="entity" id="entity" class="form-control input-lg" placeholder="Entity Type" required tabindex="3">
		</div>
    <div class="form-group">
        <label for="entityvalue" class="sr-only">Entity Value</label>
        <input type="entityvalue" name="entityvalue" id="entityvalue" class="form-control input-lg" placeholder="Entity Value" required tabindex="3">
    </div>
    <div class="form-group">
        <label for="entity2" class="sr-only">Entity Type</label>
        <input type="entity2" name="entity2" id="entity2" class="form-control input-lg" placeholder="Entity Type 2" required tabindex="3">
    </div>
    <div class="form-group">
        <label for="entityvalue2" class="sr-only">Entity Value</label>
        <input type="entityvalue2" name="entityvalue2" id="entityvalue2" class="form-control input-lg" placeholder="Entity Value 2" required tabindex="3">
    </div>
    <br>
		<hr class="colorgraph">
        <button class="btn btn-lg btn-primary btn-block" type="submit">Submit</button>
      </form>
    <br>
  <button class="btn btn-lg btn-primary btn-block" id="speak" onclick="speechSynthesis.speak(new SpeechSynthesisUtterance('<?php echo $_SESSION['message']; ?>'));" >Speak</button>
  </div>

  </body>
</html>



