<?php
//Template Name: Test Template

get_header();
?>


<?php
$result= get_industry_and_cats_api(154);

echo "<pre>";
print_r($result);
echo "</pre>";

//echo json_encode($result);
?>



<?php get_footer(); ?>