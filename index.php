<?php
 
// Connect to database
// Server - localhost
// Username - root
// Password - empty
// Database name = xmldata
$conn = mysqli_connect("localhost", "root", "", "nwp");
 
$affectedCardRow = 0;
$affectedCardPriceRow = 0;
$affectedCardPictureRow = 0;

function deleteData ($conn, $table) {
    $sql = "delete from " . $table;
    $result = mysqli_query($conn, $sql);
}

function clearFileContent ($fileContents) {

    // Replace
    $fileContents = str_replace('&nbsp;', '', $fileContents);
    $fileContents = str_replace('&', '', $fileContents);
    $fileContents = str_replace('<>', '&lt;&gt;', $fileContents);

    // Add the CDATA tags //dodati foreach
    $fileContents = str_replace('<TechnicalDescription>', '<TechnicalDescription><![CDATA[', $fileContents);
    $fileContents = str_replace('</TechnicalDescription>', ']]></TechnicalDescription>', $fileContents);
    $fileContents = str_replace('<MarketingDescription>', '<MarketingDescription><![CDATA[', $fileContents);
    $fileContents = str_replace('<MarketingDescription xml:space="preserve">', '<MarketingDescription xml:space="preserve"><![CDATA[', $fileContents);
    $fileContents = str_replace('</MarketingDescription>', ']]></MarketingDescription>', $fileContents);

    return $fileContents;
}

// insert or update price
function insertPrice ($conn, $card_id, $currency, $vat, $selling_price, $selling_price_without_vat) {
    $sql = "SELECT id FROM price WHERE card_id = $card_id  AND  currency = '$currency'";
    $result1 = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result1) > 0) {

        // set update column and value
        $update = 'selling_price = selling_price AND selling_price_without_vat = selling_price_without_vat';

        if (! empty($selling_price)) {
            $update = 'selling_price = ' . $selling_price;
        } else if (! empty($selling_price_without_vat)) {
            $update = 'selling_price_without_vat = ' . $selling_price_without_vat;
        }

        // output data of each row
        while($row = mysqli_fetch_assoc($result1)) {
            $sql ="UPDATE price SET " . $update .  " WHERE id = " . $row["id"];
            $result2 = mysqli_query($conn, $sql);
        }
    } else {
        $sql = "INSERT INTO price (card_id, currency, vat, selling_price, selling_price_without_vat) VALUES ('"
                . $card_id . "','" . $currency . "','"  . $vat . "','" . $selling_price . "','" . $selling_price_without_vat . "')";
        $result3 = mysqli_query($conn, $sql);

        if (! empty($result3)) {
            return 1;
        }
    }
    return 0;
}

// insert or update price
function insertPicture ($conn, $card_id, $picture_id, $picture_description, $picture_file, $picture_default) {
    $sql = "INSERT INTO picture (card_id, picture_id, picture_description, picture_file, picture_default) VALUES ('"
            . $card_id . "','" . $picture_id . "','"  . $picture_description . "','" . $picture_file . "','" . $picture_default . "')";
    $result5 = mysqli_query($conn, $sql);

    if (! empty($result5)) {
        return 1;
    }
    return 0;
}

// create rss
function createRssXml ($conn) {

    $base_url = "https://green.techsaver.hr";

    $dom = new DOMDocument();

        $dom->encoding = 'utf-8';

        $dom->xmlVersion = '1.0';

        $dom->formatOutput = true;

    $xml_file_name = 'dpa_product_catalog_sample_feed_rss.xml';

        $rss = $dom->createElement('rss');

        $attr_rss = new DOMAttr('xmlns:g', 'http://base.google.com/ns/1.0');

        $rss->setAttributeNode($attr_rss);

        $attr_rss_version = new DOMAttr('version', '2.0');

        $rss->setAttributeNode($attr_rss_version);

        $channel_node = $dom->createElement('channel');

            $child_node_title = $dom->createElement('title', 'Green Tech');
            $channel_node->appendChild($child_node_title);

            $child_node_link = $dom->createElement('link', $base_url);
            $channel_node->appendChild($child_node_link);

            $child_node_description = $dom->createElement('description', 'An example item from the feed');
            $channel_node->appendChild($child_node_description);

            $sql = "SELECT c.code id, c.name_hr title, c.description description, CONCAT('https://green.techsaver.hr/', REPLACE(REPLACE(REPLACE(REPLACE(LOWER(SUBSTRING_INDEX(c.name_hr, ';', 2)), ' ', '-'), ';', ''), '/', ''), '.', ''), '-', c.card_id) link, " .
                   "       CONCAT('https://green.techsaver.hr/', pi.picture_file) image_link, " .
                   "       CASE WHEN INSTR(name_hr, 'HP') > 0 THEN 'HP' WHEN INSTR(name_hr, 'Epson') > 0 THEN 'Epson' WHEN INSTR(name_hr, 'Dell') > 0 THEN 'Dell' WHEN INSTR(name_hr, 'Lenovo') > 0 THEN 'Lenovo' " .
                   "       WHEN INSTR(name_hr, 'Canon') > 0 THEN 'Canon' WHEN INSTR(name_hr, 'Fujitsu') > 0 THEN 'Fujitsu' WHEN INSTR(name_hr, 'Lexmark') > 0 THEN 'Lexmark' ELSE null END brand, " .
                   "       'new' as card_condition, CASE WHEN c.count > 0 THEN 'in stock' ELSE null END availability, CONCAT(FORMAT(pr.selling_price, 2), ' ', pr.currency) price " .
                   "  FROM card c, price pr, picture pi WHERE pr.card_id = c.id AND pi.card_id = c.id AND pi.picture_default = 1;";
            $result6 = mysqli_query($conn, $sql);

            while($row = mysqli_fetch_assoc($result6)) {            
            
                $child_node_item = $dom->createElement('item');

                    $child_node_item_id = $dom->createElement('g:id', $row["id"]);
                    $child_node_item->appendChild($child_node_item_id);

                    $child_node_item_title = $dom->createElement('g:title', $row["title"]);
                    $child_node_item->appendChild($child_node_item_title);

                    $child_node_item_description = $dom->createElement('g:description', $row["description"]);
                    $child_node_item->appendChild($child_node_item_description);

                    $child_node_item_link = $dom->createElement('g:link', $row["link"]);
                    $child_node_item->appendChild($child_node_item_link);

                    $child_node_item_image_link = $dom->createElement('g:image_link', $row["image_link"]);
                    $child_node_item->appendChild($child_node_item_image_link);

                    $child_node_item_brand = $dom->createElement('g:brand', $row["brand"]);
                    $child_node_item->appendChild($child_node_item_brand);

                    $child_node_item_condition = $dom->createElement('g:condition', 'new');
                    $child_node_item->appendChild($child_node_item_condition);

                    $child_node_item_availability = $dom->createElement('g:availability', $row["availability"]);
                    $child_node_item->appendChild($child_node_item_availability);

                    $child_node_item_price = $dom->createElement('g:price', $row["price"]);
                    $child_node_item->appendChild($child_node_item_price);
/*
                    $child_node_item_shipping = $dom->createElement('g:shipping', '');
                    $child_node_item->appendChild($child_node_item_shipping);

                    $child_node_item_country = $dom->createElement('g:country', 'UK');
                    $child_node_item->appendChild($child_node_item_country);

                    $child_node_item_service = $dom->createElement('g:service', 'Standard');
                    $child_node_item->appendChild($child_node_item_service);

                    $child_node_item_price = $dom->createElement('g:price', '4.95 GBP');
                    $child_node_item->appendChild($child_node_item_price);

                    $child_node_item_shipping = $dom->createElement('g:shipping', '');
                    $child_node_item->appendChild($child_node_item_shipping);

                    $child_node_item_google_product_category = $dom->createElement('g:google_product_category', 'Animals &gt; Pet Supplies');
                    $child_node_item->appendChild($child_node_item_google_product_category);

                    $child_node_item_custom_label_0 = $dom->createElement('g:custom_label_0', 'Made in Waterford, IE');
                    $child_node_item->appendChild($child_node_item_custom_label_0);
*/

                $channel_node->appendChild($child_node_item);

            }

        $rss->appendChild($channel_node);

        $dom->appendChild($rss);

    $dom->save($xml_file_name);

    $dom->save($xml_file_name);

    echo "$xml_file_name has been successfully created";
}

// Get file content as string
$fileCardContents = file_get_contents('ExportZasobUniversalEN2019.xml');

$fileCardContents = clearFileContent ($fileCardContents);

// Load xml file else check connection
$xmlCard = simplexml_load_string($fileCardContents)
    or die("Error: Cannot create object");

// delete data
deleteData($conn, "picture");
deleteData($conn, "price");
deleteData($conn, "card");

// Assign values products
foreach ($xmlCard->children() as $row) {
    $card_id = $row->cardid;
    $code = $row->code;
    $name = $row->name;
    $name_hr = $row->nameHR;
	$text = $row->text;
	$storage = $row->storage;
	$storage_hr = $row->storageHR;
	$count = $row->count;
	$unit = $row->unit;
	$warranty = $row->warranty;
	$action = $row->action;

    // SQL query to insert data into xml table 
    $sql = "INSERT INTO card (card_id, code, name, name_hr, text, storage, storage_hr, count, unit, warranty, action) VALUES ('"
    . $card_id . "','". $code . "','"
    . $name . "','" . $name_hr . "', '" . $text . "', '" . $storage . "', '" 
    . $storage_hr . "', '" . $count . "', '" 
    . $unit . "', '" . $warranty . "', '" . $action . "')";

    $result = mysqli_query($conn, $sql);

    if (! empty($result)) {
        $affectedCardRow ++;
        $last_id = $conn->insert_id;
        // prices
        foreach ($row->prices->children() as $price) {
            $currency = $price->currency;
            $vat = $price->vat;
            $selling_price_without_vat = $price->sellingpricewithoutvat;
            $selling_price = $price->sellingprice;

            if ($currency=='HRK') {
                //echo  'Card: ' . $card_id . ' Currency: ' . $currency . ' Selling price: ' . $selling_price . ' Selling price without vat: ' . $selling_price_without_vat .  '<br>';

                // inseet prices HR
                // SQL query to insert data into xml table 
                /*$sql = "INSERT INTO price (card_id, currency, vat, selling_price, selling_price_without_vat) VALUES ('"
                    . $last_id . "','" . $currency . "','"  . $vat . "','" . $selling_price . "','" . $selling_price_without_vat . "')";

                $result = mysqli_query($conn, $sql);
                
                if (! empty($result)) {
                    $affectedCardPriceRow ++;
                }*/
                $affectedCardPriceRow += insertPrice($conn, $last_id, $currency, $vat, $selling_price, $selling_price_without_vat);

            }
        }
        // pictures
        foreach ($row->pictures->children() as $picture) {
            $picture_id = $picture->pictureid;
            $picture_description = $picture->picturedescription;
            $picture_file = $picture->picturefile;
            $picture_default = $picture->picturedefault;

            $affectedCardPictureRow += insertPicture($conn, $last_id, $picture_id, $picture_description, $picture_file, $picture_default);
        }
    }
}

createRssXml($conn);
?>
 
<center><h2>Pohrana podataka</h2></center>

<?php
if ($affectedCardRow > 0) {
    $message = "Card: " . $affectedCardRow . " records inserted.<br/>Card price: " . $affectedCardPriceRow . " records inserted. <br/>Card picture: " . $affectedCardPictureRow . " records inserted.";
} else {
    $message = "No records inserted";
}
 
?>
<style>
    body { 
        max-width:550px;
        font-family: Arial;
    }
    .affected-row {
        background: #cae4ca;
        padding: 10px;
        margin-bottom: 20px;
        border: #bdd6bd 1px solid;
        border-radius: 2px;
        color: #6e716e;
    }
    .error-message {
        background: #eac0c0;
        padding: 10px;
        margin-bottom: 20px;
        border: #dab2b2 1px solid;
        border-radius: 2px;
        color: #5d5b5b;
    }
</style>
 
<div class="affected-row">
    <?php  echo $message; ?>
</div>
 
<?php if (! empty($error_message)) { ?>
 
<div class="error-message">
    <?php echo nl2br($error_message); ?>
</div>
<?php } ?>