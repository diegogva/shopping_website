<?php
  
  /*********** AMAZON SEARCH ***********/

  // AWS Access Key ID
  $aws_access_key_id = "access_key";

  // AWS Secret Key
  $aws_secret_key = "secret_key";

  // Associate Tag
  $AssociateTag = "associate_tag";

  // Region
  $endpoint = "webservices.amazon.com";

  $uri = "/onca/xml";

  $Keywords = "";
  $SearchIndex = "";
  $Sort = "";
  $page_number = 1;

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Keywords = $_POST['keywords'];
    $SearchIndex = $_POST['search_index'];
    $Sort = $_POST['sort'];
  } else {
    $Keywords = $_GET['keywords'];
    $SearchIndex = $_GET['search_index'];
    $Sort = $_POST['sort'];
    $page_number = $_GET['page_number'];
  }

  $params = array(
    "Service" => "AWSECommerceService",
    "Operation" => "ItemSearch",
    "AWSAccessKeyId" => $aws_access_key_id,
    "AssociateTag" => $AssociateTag,
    "SearchIndex" => $SearchIndex,
    "Keywords" => $Keywords,
    "ResponseGroup" => "EditorialReview,Images,ItemAttributes,Offers",
    "ItemPage" => $page_number
  );

  // Set current timestamp if not set
  if (!isset($params["Timestamp"])) {
    $params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
  }

  // Sort the parameters by key
  ksort($params);

  $pairs = array();

  foreach ($params as $key => $value) {
    array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
  }

  // Generate the canonical query
  $canonical_query_string = join("&", $pairs);

  // Generate the string to be signed
  $string_to_sign = "GET\n".$endpoint."\n".$uri."\n".$canonical_query_string;

  // Generate the signature required by the Product Advertising API
  $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $aws_secret_key, true));

  // Generate the signed URL
  $request = 'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);

  // Get XML contents into a string
  $response = file_get_contents($request);

  // Parse XML string from the request
  $parsed_xml = simplexml_load_string($response);

  /*********** AMAZON SEARCH ***********/

  printPage($parsed_xml, $SearchIndex, $Keywords, $Sort, $page_number);

  /**************** ALL HELPER FUNCTIONS BELOW ****************/

  function printSearchBar() {
    print(
      '<form method="POST" action="amazon_search.php">
        Sort: 
        <input type="radio" name="sort" value="relevance" checked>Relevance</input>
        <input type="radio" name="sort" value="price">Price</input>
        </br>
        
        <select name=search_index>
          <option value="All">All</option>
          <option value="Appliances">Appliances</option>
          <option value="Arts">Arts and Crafts</option>
          <option value="Automotive">Automotive</option>
          <option value="Baby">Baby</option>
          <option value="Beauty">Beauty</option>
          <option value="Blended">Blended</option>
          <option value="Books">Books</option>
          <option value="Collectibles">Collectibles</option>
          <option value="Electronics">Electronics</option>
          <option value="Fashion">Fashion</option>
          <option value="FashionBaby">Fashion - Baby</option>
          <option value="FashionBoys">Fashion - Boys</option>
          <option value="FashionGirls">Fashion - Girls</option>
          <option value="FashionMen">Fashion - Men</option>
          <option value="FashionWomen">Fashion - Women</option>
          <option value="GiftCards">Gift Cards</option>
          <option value="Grocery">Grocery</option>
          <option value="HealthPersonalCare">Health/Personal Care</option>
          <option value="HomeGarden">Home Garden</option>
          <option value="Industrial">Industrial</option>
          <option value="KindleStore">Kindle Store</option>
          <option value="LawnAndGarden">Lawn and Garden</option>
          <option value="Luggage">Luggage</option>
          <option value="MP3Downloads">MP3 Downloads</option>
          <option value="Magazines">Magazines</option>
          <option value="Mercahnts">Merchants</option>
          <option value="Mobileapps">Mobile Apps</option>
          <option value="Movies">Movies</option>
          <option value="Music">Music</option>
          <option value="MusicalInstruments">Musical Instruments</option>
          <option value="OfficeProducts">Office Products</option>
          <option value="PCHardware">PC Hardware</option>
          <option value="PetSupplies">Pet Supplies</option>
          <option value="Software">Software</option>
          <option value="SportingGoods">Sporting Goods</option>
          <option value="Tools">Tools</option>
          <option value="Toys">Toys</option>
          <option value="UnboxVideo">Unboxed Videos</option>
          <option value="VideoGames">Video Games</option>
          <option value="Wine">Wine</option>
          <option value="Wireless">Wireless</option>
        </select> 
        <input type="text" name="keywords">
        <input type="submit">
      </form>'
    );  
  }

  function printPageNavigation($SearchIndex, $Keywords, $Sort, $page_number) {
    $formatted_search_index = str_replace(" ", "%20", $SearchIndex);
    $formatted_keywords = str_replace(" ", "%20", $Keywords);
    $previous_page_number = $page_number - 1;
    $next_page_number = $page_number + 1;
    if ($page_number == 1) {
      print("<< < ");
    } else {
      print("<a href=/amazon_search.php?search_index=".$formatted_search_index."&keywords=".$formatted_keywords."&sort=".$Sort."&page_number=1><<</a> ");
      print("<a href=/amazon_search.php?search_index=".$formatted_search_index."&keywords=".$formatted_keywords."&sort=".$Sort."&page_number=".$previous_page_number."><</a> ");
    }
    for ($x = 1; $x <= 5; $x++) {
      if ($x == $page_number) {
        print($x." ");
      } else {
        print("<a href=/amazon_search.php?search_index=".$formatted_search_index."&keywords=".$formatted_keywords."&sort=".$Sort."&page_number=".$x.">$x</a> ");
      }
    }
    if ($page_number == 5) {
      print("> >>");
    } else {
      print("<a href=/amazon_search.php?search_index=".$formatted_search_index."&keywords=".$formatted_keywords."&sort=".$Sort."&page_number=".$next_page_number.">></a> ");
      print("<a href=/amazon_search.php?search_index=".$formatted_search_index."&keywords=".$formatted_keywords."&sort=".$Sort."&page_number=5>>></a> ");
    }
  }

  function printHTMLHeader() {

    print("<!DOCTYPE>");
    print("<html>");
    print("<style>");
    print("p.padding { padding-left:1cm; }");
    print("</style>");
    print("<body link='#003399' alink='#FF9933' vlink='#996633'>"); 
  }

  function printHTMLFooter() {
    print("</body>");
    print("</html>");
  }


  function printPage($parsed_xml, $SearchIndex, $Keywords, $Sort, $page_number) {
    
    $count = 10 * ($page_number - 1) + 1;

    printHTMLHeader();

    print("<center>");
    printSearchBar();
    printPageNavigation($SearchIndex, $Keywords, $Sort, $page_number);
    print("<br><br>");
    
    print("<table width='65%' style='margin-left:20px;'>");
    
    foreach ($parsed_xml->Items->Item as $current) {
      print("<tr><td valign='top' align='left'><b>".$count.". <a href=".$current->DetailPageURL.">".$current->ItemAttributes->Title."</a></b>");
      
      if ($current->ItemAttributes->Author != NULL) {
        print("<font size='-1'> by ".$current->ItemAttributes->Author[0]."</font>");
      }
      
      print("<br><table><tbody><tr><td><div style='float:left;'>");
      
      print("<a href=".$current->DetailPageURL."><img src=".$current->MediumImage->URL."></a>");
      
      print("</div><div style='float:left;'><p class='padding'><table><tbody>");
      
      if ($current->ItemAttributes->ListPrice->FormattedPrice != NULL) { 
        print("<tr><td align='right'><u>Amazon Price</u>: </td><td algin='left'>".$current->ItemAttributes->ListPrice->FormattedPrice."</td></tr>");
      } else {
        print("<tr><td align='right'><u>Amazon Price</u>: </td><td align='left'>N/A</td></tr>");
      }
      if ($current->ItemAttributes->Binding != NULL) {
        print("<tr><td align='right'><u>Category</u>: </td><td align='left'>".$current->ItemAttributes->Binding."</td></tr>");
      }
      if ($current->ItemAttributes->Publisher != NULL) {
        print("<tr><td align='right'><u>Company</u>: </td><td align='left'>".$current->ItemAttributes->Publisher."</td></tr>");
      }
      if ($current->ItemAttribtues->Brand != NULL) {
        print("<tr><td align='right'><u>Brand</u>: </td><td align='left'>".$current->ItemAttributes->Brand."</td></tr>");
      }
      if ($current->ItemAttributes->ReleaseDate != NULL) {
        print("<tr><td align='right'><u>Release Date</u>: </td><td align='left'>".$current->ItemAttributes->ReleaseDate."</td></tr>");
      }
      if ($current->ItemAttributes->ISBN != NULL) {
        print("<tr><td align='right'><u>ISBN</u>: </td><td align='left'>".$current->ItemAttributes->ISBN."</td></tr>");
      }
      if ($current->OfferSummary->LowestNewPrice->FormattedPrice != NULL) {
        print("<tr><td align='right'><u>Lowest New Price</u>: </td><td align='left'>".$current->OfferSummary->LowestNewPrice->FormattedPrice."</td></tr>");
      }
      if ($current->OfferSummary->LowestUsedPrice->FormattedPrice != NULL) {
        print("<tr><td align='right'><u>Lowest Used Price</u>: </td><td align='left'>".$current->OfferSummary->LowestUsedPrice->FormattedPrice."</td></tr>");
      }

      print("</tbody></table></font></p></div></td></tr></tbody></table>");

      if ($current->EditorialReviews->EditorialReview[0]->Content != NULL) {
        print("<u>Product Description</u>: <br>".$current->EditorialReviews->EditorialReview[0]->Content);
        print("<br>");
      }
      print("<hr></td></tr>");
      $count += 1;
    }

    print("</table></center>");
    
    print("<center><br>");
    printPageNavigation($SearchIndex, $Keywords, $Sort, $page_number);
    print("<br><br></center>");

    printHTMLFooter();
  }
?>

