<?php
include("include/header.php");
include "../mydash/includes/connection.php";
	  	$sql_course = "select * FROM university where id='".unserialize(base64_decode($_GET['id']))."'";
		$result_course = mysqli_query($con,$sql_course);
		$rs_course=mysqli_fetch_array($result_course);
		

?>


<link rel="preload" href="_next/static/css/d4c0d928aec8adce.css" as="style"/><link rel="stylesheet" href="_next/static/css/d4c0d928aec8adce.css" data-n-g=""/><link rel="preload" href="_next/static/css/f813b3783fb8448c.css" as="style"/><link rel="stylesheet" href="_next/static/css/f813b3783fb8448c.css" data-n-p=""/><noscript data-n-css=""></noscript><script defer="" nomodule="" src="_next/static/chunks/polyfills-5cd94c89d3acac5f.js"></script><script src="_next/static/chunks/webpack-9b312e20a4e32339.js" defer=""></script><script src="_next/static/chunks/framework-a87821de553db91d.js" defer=""></script><script src="_next/static/chunks/main-fc7d2f0e2098927e.js" defer=""></script><script src="_next/static/chunks/pages/_app-2f25f84b5e3a9f86.js" defer=""></script><script src="_next/static/chunks/261-9419b3d063c1f58b.js" defer=""></script><script src="_next/static/chunks/pages/university-08d6b78ebfbe9a47.js" defer=""></script><script src="_next/static/8RJVqHmHkgpZNwNE2wVHe/_buildManifest.js" defer=""></script><script src="_next/static/8RJVqHmHkgpZNwNE2wVHe/_ssgManifest.js" defer=""></script><script src="_next/static/8RJVqHmHkgpZNwNE2wVHe/_middlewareManifest.js" defer=""></script>


<body>
    
<div class="Layout_container__S4aNf"><main><div><div class="university_universityIntro__ncWjl"><div class="university_banner__MkfxO"><img class="img-fluid" src="../<?php echo $rs_course['image'] ?>" alt="Image1"></div><div class="university_UniCard__t2_QV"><div class="UniCard_container__BnklS"><div class="UniCard_logo__NRK_F"><img src="assets/images/Logo.webp" alt="uniLogo"/></div><div class="UniCard_uniInfo__exgp0"><p style="color:#00355B;font-weight:700"><?php echo $rs_course['name'] ?></p><hr/><div class="UniCard_info__S_3WC"><p><strong>Founded:</strong> 1935<!-- --></p><p>Russia</p></div></div></div></div>




<div>
<?php echo $rs_course['description']?>
<button class="btn btn-success consultation2 popcrm" data-toggle="modal" data-target="#myModal">Get your consultation</button>
</div>


<script id="__NEXT_DATA__" type="application/json">{"props":{"pageProps":{}},"page":"/university","query":{},"buildId":"8RJVqHmHkgpZNwNE2wVHe","nextExport":true,"autoExport":true,"isFallback":false,"scriptLoader":[]}</script>

</body>





<?php 
include("include/footer.php");
?>