<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("WD_WEBDAV"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:webdav",
	"",
	Array(
		"IBLOCK_TYPE" => "lb", 
		"IBLOCK_ID" => "73", 
		
		"SEF_MODE" => "Y", 
		"SEF_FOLDER" => "/lib_docs/", 
		"BASE_URL" => "/lib_docs/", 
		
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600", 
		"SET_TITLE" => "Y", 
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>