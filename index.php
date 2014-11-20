<?php
  $twitterUser = $_GET['twitter'];
  
  if ( $twitterUser ) {
   
    // get twitter avatar and pixelate it
    require_once('php/config.php'); // change sample.config.php to config.php with your own creds, babes!
    require_once('php/TwitterAPIExchange.php');
  
    $requestMethod = 'GET';
    $url = 'https://api.twitter.com/1.1/users/show.json';
    $getUser = '?screen_name=' . $twitterUser;

    $twitterRequest = new TwitterAPIExchange($settings);
    $json = $twitterRequest->setGetfield($getUser)
      ->buildOauth($url, $requestMethod)
      ->performRequest();

    $result = json_decode($json);
    $avatarURL = $result->profile_image_url;
  
    $data = file_get_contents($avatarURL);
    $avatarBase64 = 'data:image/png;base64,' . base64_encode($data);
    
    // if twitter handle exists and can get image, hide original pixelfies
    $hidePixelfies = true;
    $galleryClass = 'twitter';
  }
  else {
    // thank you babes for being so awesome and pixelated
    $selfies = array();
    foreach (glob('selfies/*.png') as $filename) { 
      $name = str_replace('selfies/', '', $filename);
      $name = str_replace('.png', '', $name);
      array_push($selfies, $name);
    }
  
    function getRGB( $color ) {
      $r = ($color >> 16) & 0xFF;
      $g = ($color >> 8) & 0xFF;
      $b = $color & 0xFF;
      return $r . ',' . $g . ',' . $b;
    }
  
    $galleryClass = 'babes';
  }
?>

<!doctype html>
<html lang="en">
<meta charset="UTF-8">
  <title>~*pixelfies*~ real selfies of real people with real pixels</title>
  <link rel="icon" type="image/png" href="assets/favicon.png" />
  <link rel="stylesheet" type="text/css" href="css/style.css" />
</head>

<body>
  <div id="gallery">

  <div id="about-this-thing">
    <h1>#pixelfies</h1>
    <p>real people<br />
    real selfies<br />
    real drama</p>
    <p>made by<br /><a href="http://twitter.com/jennschiffer">@jennschiffer</a></p>
    <div id="controls">
    <?php if ( !$hidePixelfies ) { ?> 
      <button id="random">random pixelfie</button>
      <p>#<span id="hex">ffffff</span></p>
    <?php }
    else { ?>
      <p>
        <input type="submit" value="Save Your Pixelfie!" id="save-pixelfie" />
      </p>
  <?php } ?>
    </div>
  </div>

    <?php
    /*** NOT A TWITTER REQUEST, SO SHOW RANDO BABES OH YEAHHHH ***/
  if ( !$hidePixelfies ) { ?>
   <!-- <p>blah blah something about twitter handle</p>-->

    <div id="pixelfies" class="<?php echo $galleryClass; ?>">
      <?php
    foreach ( $selfies as $selfie ) {
      
      $filename = 'selfies/' . $selfie . '.png';
      $png = imagecreatefrompng($filename);
      $imageSize = getimagesize($filename);
      $imageWidth = $imageSize[0];
      $imageHeight = $imageSize[1];
      
      echo '<div id="' . $selfie . '" class="pixelfie">';
      
      for ( $row = 1; $row < $imageHeight; $row++ ) {
    
        for ( $column = 1; $column < $imageWidth; $column++ ) {
      echo '<div class="pixel" style="background:rgb(' . getRGB( imagecolorat($png, $column, $row) ) . ');"></div>';
        }
        echo '<div class="break"></div>';    
      }
      echo '</div>';
    }
      ?>
    </div>
    <?php
  } else {
  /*** WE HAVE A TWITTER REQUEST, SO SHOW THAT IMAGE WOWOWOW ***/
    ?>
  
    <div id="twixelfies">
    <p><a href="/">Check out the original #pixelfies babes!</a></p>

    <canvas id="twixelfie" width="576" height="576">Your browser doesn't support canvas >:/</canvas>
    </div>
  
    <?php } ?>
  
  </div>
    
<script type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
<script type="text/javascript">
$(function(){

  var $body = $('body');
  $gallery = $('#pixelfies'),
  $pixelfies = $('.pixelfie'),
  $buttonParty = $('#party'),
  $buttonRandom = $('#random'),
  $alert = $('#alert'),
  $hex = $('#hex'),
  $saveButton = $('#save-pixelfie');
  
  var avatarBase64 = '<?php echo $avatarBase64; ?>';   
  
  var galleryClass = $gallery.attr('class'),
  index = -1,
  colorFlashMode
  loopOn = true;
  
  // get random index but never same two in a row
  var getRandomIndex = function() {
    var newIndex = Math.floor( Math.random() * ($pixelfies.length + 1) );
    if ( newIndex == index ) {
      newIndex--;
      if ( newIndex < 0 ) {
        newIndex = $pixelfies.length -1;
      }
    }
    return newIndex;
  };
  
  // get next pixelfie
  var nextPixelfiePlz = function() {
    index = getRandomIndex();      
    var $currentPixelfie = $pixelfies.eq(index).show().addClass('current');
    $pixels = $currentPixelfie.find('.pixel');
    var $currentPixelfieId = $currentPixelfie.attr('id');
    var $currentPixelfiePixels = $currentPixelfie.find('.pixel');
    $body.removeAttr('class').addClass($currentPixelfieId);
    
    bindPixelMouseover();
  };
  
  // get rgb from image data
  var getRGBColor = function(imageData) {
    var opacity = imageData[3]/255;
    return 'rgba(' + imageData[0] + ', ' + imageData[1] + ', ' + imageData[2] + ', ' + opacity + ')';
  };
  
  // rgb to hex
  var rgbToHex = function( rgb ) {
    var rgbArray = rgb.substr(4, rgb.length - 5).split(',');
    var hex = "";
    for ( var i = 0; i <= 2; i++ ) {
  var hexUnit = parseInt(rgbArray[i]).toString(16);
  if ( hexUnit.length == 1 ) {
    hexUnit = '0' + hexUnit;
  }
  hex += hexUnit;
    }
    return hex;
  };
  
  // background change on hover
  var bindPixelMouseover = function() {
    $('.current').find('.pixel').on('mouseover', function(e){
  loopOn = false;
  var newColor = $(this).css('background-color');
  $body.css('background-color', newColor );
  $hex.text( rgbToHex(newColor) );
    });
  };
  
  // TODO background change on click
  
  var showRandomPixelfie = function() {
  var currentPixels = $('.current').find('.pixel');
    currentPixels.off();  
    $('.current').removeClass('current').hide();
    nextPixelfiePlz(); 
  }
    
  // randomize on random button click
  $buttonRandom.click(showRandomPixelfie);
  
  // save on save button click
  $saveButton.click(function(){
    var img = $twixelfie[0].toDataURL('image/png');
    window.open(img, '_blank');
  });
    
    
  /*** INIT PIXELFIES PROJECT ***/
  
  // only do this if there is an avatar URL
  if ( avatarBase64 != '' ) { 
   var $twixelfie = $('#twixelfie');
   var ctx = $twixelfie[0].getContext('2d');
   var twixelfie = new Image();
   twixelfie.src = avatarBase64;
   twixelfie.onload = function(){
     
    var coordX = 0,
     coordY = 0,
   pixelSize = 12;
   
  // get image on memory canvas so we can grab data
     var memoryCanvas = document.createElement('canvas');
     var memoryCtx = memoryCanvas.getContext('2d');
     memoryCtx.drawImage(twixelfie,0,0);
    
    // for each row draw pixel at y+i
    for ( var x = 0; x < 48; x++ ) {
  for ( var y = 0; y < 48; y++ ) {
    var pixelData = memoryCtx.getImageData(x,y,1,1).data;
    var pixelRGB = getRGBColor(pixelData);
    
    //draw rect
    ctx.fillStyle = pixelRGB;
    ctx.fillRect(coordX,coordY,pixelSize,pixelSize);

  coordY = coordY + pixelSize;
  }
  coordY = 0;
  coordX = coordX + pixelSize;
    }
   };
   
  } 
  else {
  // init rando babes
  showRandomPixelfie(); 
  }
     
});
</script>

<!-- just some analytics, whatever -->
<script src="//pmetrics.performancing.com/js" type="text/javascript"></script>
<script type="text/javascript">try{ clicky.init(14721); }catch(e){}</script>
<noscript><p><img alt="Performancing Metrics" width="1" height="1" src="//pmetrics.performancing.com/14721ns.gif" /></p></noscript>
</body>
</html>