<?

/*
 * Основная функция  - возврат древовидной структуры содержимого инфоблока*/

/**@var $arParams*/
/**@var $arResult*/

CModule::IncludeModule('iblock');

$arSecrionFilter = null;
$arParams['SECTION_FILTER'] ? $arSecrionFilter = $arParams['SECTION_FILTER'] : array();
//Возможно, тут нужна другая
// логика, например предусмотреть обязательные поля

$arSectionSelect = null;
$arParams['SECTION_SELECT'] ? $arSectionSelect = $arParams['SECTION_SELECT'] : array();
//Возможно, тут нужна другая
// логика, например предусмотреть обязательные поля

$resSections = CIBlockSection::GetTreeList(
	$arSecrionFilter,
	$arSectionSelect
);
$arSectionsCatalog = array();
while($arSection = $resSections->Fetch()){
	$arSectionsCatalog[$arSection['ID']] = $arSection;//Получаем справочник секций
}

//Возможно, тут следует предусмотреть возможность отключения через параметры
$resElements = CIBlockElement::GetList(
	array(),
	array(),
	false,
	false,
	array()
);

while($arElement = $resElements->Fetch()){
	$arElementsCatalog[$arElement['ID']] = $arElement;//Получаем справочник элементов
}


foreach($arSectionsCatalog as $id => $SectionArray){//Заполнение
	if($SectionArray['DEPTH_LEVEL']==1){
		$arResult['SECTIONS_CONTENT'][$SectionArray['ID']] = $SectionArray;
		unset($arSectionsCatalog["ID"]);//Чтобы в дальнейшем было легче искать
	}
}

function SectionsTreeSort($sectionID){
	global $arSectionsCatalog;
	$resultArray = array();
	if(is_array($arSectionsCatalog)){
		foreach($arSectionsCatalog as $key => $catalog){
			if($catalog['SECTION_ID'] == $sectionID){
				$resultArray[$catalog['ID']] = $catalog;
				$resultArray[$catalog['ID']]['SUBSECTIONS'] = SectionsTreeSort($catalog['ID']);
			}
		}
	}

	return $resultArray;
}

foreach($arResult['SECTION_CONTENT'] as $key => $section){
	$section['SUBSECTIONS'] = SectionsTreeSort($section['ID']);
}




