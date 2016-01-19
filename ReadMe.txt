############################# installation guide ################################

*1. Copy all files from the "copy_this" folder and put it in shop directory


*2. Open the file "shop directory" >> checkout_success.php


*3. Put at the end of file,  before  "require(DIR_WS_INCLUDES . 'template_bottom.php');" following line :

     include_once(DIR_WS_MODULES . "tracking_pixel/tp_checkout.php");


################################################################################
########################################### For Testing

 www.[yourshopname]/datafeedview.php?dataFeed[secret]=efab5d86e79601fc0e4e99bcd56d2006&dataFeed[fnc]=getFeed
 www.[yourshopname]/datafeedview.php?dataFeed[secret]=efab5d86e79601fc0e4e99bcd56d2006&dataFeed[fnc]=getOrderProducts&dataFeed[args][id]=1
 www.[yourshopname]/datafeedview.php?dataFeed[secret]=efab5d86e79601fc0e4e99bcd56d2006&dataFeed[fnc]=getProduct&dataFeed[args][id]=1_5_1