<?php
include("include/header.php");
include "../mydash/includes/connection.php";
?>
<body>
<div class="ENQUIRY">
   <a href="#" class="btn btn-success form popcrm" data-toggle="modal" data-target="#myModal">Enquiry Now</a>
 </div>
<section class="desk">
    <div id="demo" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <li data-target="#demo" data-slide-to="0" class="active"></li>
            <li data-target="#demo" data-slide-to="1"></li>
        </ol>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img class="img-fluid" src="images/Banner.png" alt="Image1">
            </div>
            <!-- <div class="carousel-item">
                <img src="images/.webp" alt="Image2" width="1920" height="1080">
            </div> -->
        </div>
        <div id="frontpage">
        <h1 class="abroad">Studying Overseas</h1>
        <h3 class="top1">No Longer A Distant Dream</h3> 
        <h4 class="top">Top Courses | Personalized Service | Personal Counsellor</h4>
    </div>
</div>    
</section>
<section class="mobile" style="margin-top: 72px">
    <div id="demo" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <li data-target="#demo" data-slide-to="0" class="active"></li>
            <li data-target="#demo" data-slide-to="1"></li>
        </ol>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img class="img-fluid" src="images/bannermobile2.png" alt="Image1">
            </div>
            <!-- <div class="carousel-item">
                <img src="images/.webp" alt="Image2" width="1920" height="1080">
            </div> -->
        </div>
        <div id="frontpage">
        <h1 class="abroad">Studying Overseas</h1>
        <h3 class="top1">No Longer A Distant Dream</h3> 
        <h4 class="top">Top Courses | Personalized Service | Personal Counsellor</h4>
    </div>
</div>    
</section>
<section class="second">
    <h2 class="destination"><b>Popular Destinations</b></h2>
		<?php
	  	$sql_course = "select * FROM course";
		$result_course = mysqli_query($con,$sql_course);
		$rs_course=mysqli_fetch_array($result_course);
		
		do
		{
		?>
		<h4 class="desti"><?php echo $rs_course['name'] ?></h4>
		
	  <div class="container">
      <div class="row autoplay">
	  	<?php
	  	$sql = "select * FROM country where course_id='".$rs_course['id']."'";
		$result = mysqli_query($con,$sql);
		$rs=mysqli_fetch_array($result);
		
		do
		{
		?>
		
        <div class="col-lg-4">
            <div class="box">
               <img class="img-fluid flags1" src="../<?php echo $rs['image']?>">
               <h5><b><?php echo $rs['name']?></b></h5> 
               <p><?php echo $rs['title']?></p>
            </div>
        </div>
		<?php
		}
		while($rs = mysqli_fetch_array($result));
		?>

      </div>
    </div>		
		<?php
		}
		while($rs_course = mysqli_fetch_array($result_course));
		?>
    
</section>
<section class="background">
    <h2 class="bg-para"><b>Expert consultation with exceptional quality</b></h2>
    <p class="par">Just one call away : <a style="color:#fff;text-decoration:underline" href="tel:+91-9333333929">
    &nbsp;&nbsp;+91-9333333929</a></p>
    <button class="btn btn-success consultation popcrm" data-toggle="modal" data-target="#myModal"><b>Get your consultation</b></button>  
</section>
<section class="services bg" id="serv">
    <div class="container bg-element">
        <h2 class="our"><b>Our Services</b></h2>
        <div class="row service1">
            <div class="col-lg-3">
                <div class="box2">
                    <img class="img-fluid new-img" src="images/counselling.png">
                    <h5 class="img-p"><b>Genuine & Fact-based Guidance</b></h5>
                    <p class="img-p">Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus sint quasi suscipit 
                        reiciendis provident.</p>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="box2">
                    <img class="img-fluid new-img" src="images/documentation.png">
                    <h5 class="img-p"><b>Documentation & Related Fomalities</b></h5>
                    <p class="img-p">Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus sint quasi suscipit 
                        reiciendis provident.</p>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="box2">
                    <img class="img-fluid new-img" src="images/loan.png">
                    <h5 class="img-p"><b>Educational Loan<br>Assistance</b></h5>
                    <p class="img-p">Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus sint quasi suscipit 
                        reiciendis provident.</p>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="box2">
                    <img class="img-fluid new-img" src="images/pick up ansd drop.png">
                    <h5 class="img-p"><b>Pick-up & Drop on Both Sides</b></h5>
                    <p class="img-p">Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus sint quasi suscipit 
                        reiciendis provident.</p>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="box2">
                    <img class="img-fluid new-img" src="images/registration.png">
                    <h5 class="img-p"><b>Registration & Application Assistance</b></h5>
                    <p class="img-p">Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus sint quasi suscipit 
                        reiciendis provident.</p>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="box2">
                    <img class="img-fluid new-img" src="images/ticket.png">
                    <h5 class="img-p"><b>Visa and Travel Arrangements</b></h5>
                    <p class="img-p">Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus sint quasi suscipit 
                        reiciendis provident.</p>
                </div>
            </div>
            <!-- <div class="col-lg-3">
                <div class="box2">
                    <img class="img-fluid new-img" src="images/letter.png">
                    <h5 class="img-p"><b>Expert Counselling</b></h5>
                    <p class="img-p">Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus sint quasi suscipit 
                        reiciendis provident.</p>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="box2">
                    <img class="img-fluid new-img" src="images/next coaching.png">
                    <h5 class="img-p"><b>Expert Counselling</b></h5>
                    <p class="img-p">Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus sint quasi suscipit 
                        reiciendis provident.</p>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="box2">
                    <img class="img-fluid new-img" src="images/university.png">
                    <h5 class="img-p"><b>Expert Counselling</b></h5>
                    <p class="img-p">Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus sint quasi suscipit 
                        reiciendis provident.</p>
                </div>
            </div> -->
        </div>
    </div>
</section>
<section class="services  bg-serv">
    <div class="row">
        <div class="col-lg-3">
            
        </div>
        <div class="col-lg-9">
            <div class="why">
                <h2><b>What Will Choosing Us Bring To The Table</b></h2> 
                <p class="why-p"><b>Approachability & Access:</b> We are here to help anytime of the day! Our 
                team of efficient counsellors shall never make you wait as your calls will be received instantly.</p> 
                <p class="par2"><b>Fact-based Approach:</b> We don't believe in giving false-hope or baiting you
                through false offers. Whatever we suggest is based on hard facts and research. Whether it's a 
                destination or university, we will only suggest options that sticks to your particular needs,
                preferences and budget.</p>
                <p class="par2"><b>Quality:</b> We only tie-up with the best player out there, which offers
                quality and affordability. Thus, our catalog also includes only top-notch countries and instituitions.
                Moreover, our counsellors are highly skilled and trained in this line of work.</p> 
                <p class="par2"><b>A-Z Service:</b> Students will not have to worry about a single thing when we're 
                around.Right from applying on the university website, throughout the admission process, and even 
                till the very end of the journey, our officials will carry out all the necessary formalities.</p>  
                <button class="btn btn-success consultation2 popcrm" data-toggle="modal" data-target="#myModal">Get your consultation</button>
            </div>
        </div>
    </div>
</section>
<section class="services clg-bg">
    <div class="container">
        <h2 class="our"><b>Our Exclusive Colleges</b></h2>
        <div class="row">
            <div class="col-lg-4">
                <div class="tab">
				
				<?php
				$sql_course_un = "select * FROM course order by id asc";
				$result_course_un = mysqli_query($con,$sql_course_un);
				$rs_course_un=mysqli_fetch_array($result_course_un);
				$i=1;
				do
				{
				
				if($i==1)
				{
					$active="active";
				}
				else
				{
					$active="";
				}
				?>                    
				<div class="row">
                    <button class="tablinks <?php echo $active; ?>" onMouseOver="openCity(event, '<?php echo $rs_course_un['name']?>')">
                            <div class="row">
                                <div class="col-lg-4 col-4">
                                    <img class="img-fluid vector" src="images/Vector.png">
                                </div>
                                <div class="col-lg-8 col-8">
                                    <h4 class="mbbs"><?php echo $rs_course_un['name']?></h4>
                                </div>
                            </div>
                    </button>
                    </div>
				<?php
				$i++;
				}
				while($rs_course_un=mysqli_fetch_array($result_course_un));
				?>
					
                     
                     
                </div>
                <div class="vertical-line"></div>
            </div>
            <div class="col-lg-8">
			
			
								<?php
				$sql_course_un2 = "select * FROM course order by id asc";
				$result_course_un2 = mysqli_query($con,$sql_course_un2);
				$rs_course_un2=mysqli_fetch_array($result_course_un2);
				$k=1;
				do
				{
				
				if($k==1)
				{
					$active="block";
				}
				else
				{
					$active="none";
				}
				?>                    

                <div id="<?php echo $rs_course_un2['name']?>" class="tabcontent" style="display:<?php echo $active; ?>">
                    <div class="row colleges">
					
						<?php
						$sql_university_un1 = "select * FROM university where course_id='".$rs_course_un2['id']."' and active='0' order by id asc";
						$result_university_un1 = mysqli_query($con,$sql_university_un1);
						$rs_university_un1=mysqli_fetch_array($result_university_un1);
						do
						{
						?>                    
                        <div class="col-lg-4">
                            <div class="row">
                                <div class="card">
                                    <img class="univ-img" src="../<?php echo $rs_university_un1['image']?>" height="200">
                                    <h5 class="college" style="height:60px"><b><?php echo $rs_university_un1['name']?></b></h5>
                                    <p class="details1"><a href="university.php?id=<?php echo base64_encode(serialize ($rs_university_un1['id'])) ?>"><b>See details</b></a></p><i class="fa fa-angle-right angle" aria-hidden="true"></i>
                                </div> 
                            </div>
							
                            <div class="row">
                                <div class="card">
                                    <img class="univ-img" src="../<?php echo $rs_university_un1['image']?>">
                                    <h5 class="college" style="height:60px"><b><?php echo $rs_university_un1['name']?></b></h5>
                                    <p class="details"><a href="university.php?id=<?php echo base64_encode(serialize ($rs_university_un1['id'])) ?>"><b>See details1</b></a></p><i class="fa fa-angle-right angle" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
						<?php
						}
						while($rs_university_un1=mysqli_fetch_array($result_university_un1));
						?>
						
						
                       <!-- <div class="col-lg-4">
                            <div class="row">
                                <div class="card">
                                    <img class="univ-img" src="images/Peoples-Friendship-University copy.png">
                                    <h5 class="college"><b>People's Friendship University,<br>Russia</b></h5>
                                    <p class="details"><b>See details</b></p><i class="fa fa-angle-right angle" aria-hidden="true"></i>
                                </div> 
                            </div>
                            <div class="row">
                                <div class="card">
                                    <img class="univ-img" src="images/chuvash-state-university-college.jpeg">
                                    <h5 class="college"><b>Chuvash State Medical University, Russia</b></h5>
                                    <p class="details"><b>See details</b></p><i class="fa fa-angle-right angle" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
						
						
                        <div class="col-lg-4">
                            <div class="row">
                                <div class="card">
                                    <img class="univ-img" src="images/university-of-perpetual-help-system-dalta-mavenoverseas.jpeg">
                                    <h5 class="college"><b>University of Perpetual Help System DALTA, Philippines</b></h5>
                                    <p class="details"><b>See details</b></p><i class="fa fa-angle-right angle" aria-hidden="true"></i>
                                </div> 
                            </div>
                            <div class="row">
                                <div class="card">
                                    <img class="univ-img" src="images/uvgullas.jpeg">
                                    <h5 class="college"><b>UV Gullas College of Medicine, Philippines</b></h5>
                                    <p class="details"><b>See details</b></p><i class="fa fa-angle-right angle" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>-->
						
						
                        
						
						
                    </div>
                </div>
			
			
				<?php
				$k++;
				}
				while($rs_course_un2=mysqli_fetch_array($result_course_un2));
				?>
				
                 
                 
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</section>
<section class="services">
    <div class="container">
        <h2 class="destination">What other's say about us</h2>
        <div class="card3">
            <div class="box3">
                <div class="row choose">
                    <div class="col-lg-6">
                        <i class="fa fa-quote-left quote" aria-hidden="true"></i>
                        <div class="more">Eduvibe has incredible opportunities in multiple countries, 
                        they also provide counselling sessions to the students who are confused  about their 
                        career. They even have multiple offers on selective occasions for students to grab the 
                        opportunities which is very beneficial. The admission process was very smooth .Special 
                        thanks to Abhigyan Sir he has a very helpful attitude and his counselling was essential 
                        in this process.</div>
                        <img class="img-fluid student-img" src="images/second.png">
                        <h6 style="text-align:center"><b>Tejas</b></h6>
                        <p style="text-align:center">University of Perpetual Help System DALTA</p>
                    </div>
                    <div class="col-lg-6">
                        <i class="fa fa-quote-left quote" aria-hidden="true"></i>
                        <div class="more"> Hi, I am Chaitanya Pansare, I am currently studying at 
                        UV Gullas College of Medicine. I am very thankful to Eduvibe, because when all my hopes 
                        were gone, Eduvibe supported me and showed me a ray of hope. Thank you to Siddharth 
                        bhaiya, Abhigyan bhaiya, Rajeshri mam for their invaluable support. I would like to tell 
                        other medical aspirants that we can believe in Eduvibe by closing our eyes.</div>
                        <img class="img-fluid student-img" src="images/third.png">
                        <h6 style="text-align:center"><b>Chaitanya Pansare</b></h6>
                        <p style="text-align:center">UV Gullas College of Medicine, Philippines</p>
                    </div>
                    <div class="col-lg-6">
                        <i class="fa fa-quote-left quote" aria-hidden="true"></i>
                        <p class="review">I am Abhishek. I am from Asian Medical College, Kyrgyzstan. 
                        I am safe and everything is fine. Eduvibe helped me very efficiently to reach here and 
                        I hope in future also it supports me as it is supporting now.</p>
                        <img class="img-fluid student-img2" src="images/first.jpg">
                        <h6 style="text-align:center"><b>Abhishek</b></h6>
                        <p style="text-align:center">Asian Medical College, Kyrgyzstan</p>
                    </div>
                    <div class="col-lg-6">
                        <i class="fa fa-quote-left quote" aria-hidden="true"></i>
                        <p class="review">The experience was really very good. All the associates of 
                        the consultancy are really very cooperative and understanding. All of them have done a 
                        splendid job for the admission of students and also for taking their career abroad . </p>
                        <img class="img-fluid student-img2" src="images/fourth.png">
                        <h6 style="text-align:center"><b>Swarupa</b></h6>
                        <p style="text-align:center">Chuvash State Medical University</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
<?php 
include("include/footer.php");
?>