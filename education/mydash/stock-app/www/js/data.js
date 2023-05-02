 $(document).ready(function(){
						  
     $('#getUser').on('click', function() {
	  var ga = $('#ga').val();
	 // alert(ga);
	   var observed_mca_velocity = $('#observed_mca_velocity').val();
	   	if (ga=="")
			{
		alert("Please Enter Ga");
		}
		if (observed_mca_velocity=='')
			{
		alert("Please Enter Observed MCA Peak Systolic Velocity");
		}
	  
         $.ajax({
				type: "POST",
				url: service_url+"getData.php",
				dataType: "json",
				data: {ga: ga, observed_mca_velocity: observed_mca_velocity},
				//data:{ga:ga},
				success:function(data)
				    {
						//alert(data.status);
						if(data.status=='ok')
						{
						
/*						var a =data.result.ga;
						var b =data.result.observed_mca_velocity;
						var c =data.result.median_velocity;
						var d =data.result.measurement;
						alert(a);
*/						//	alert(data.result.ga);
							$("#ga").val(data.result.ga);
							$("#observed_mca_velocity").val(data.result.observed_mca_velocity);
							$("#median_velocity").val(data.result.median_velocity);
							$("#measurement").val(data.result.measurement);
							//alert(data.result.measurement);
						}else
						{
							alert("Data Not Found...");
							//$("#ga").val('');
							//$("#observed_mca_velocity").val('');
							$("#median_velocity").val('');
							$("#measurement").val('');

						}
					}
				});
                  
	});

});
 
