<?

/*
 * Основная функция  - возврат древовидной структуры содержимого инфоблока*/

/**@var $arParams */
/**@var $arResult */

CModule::IncludeModule( 'iblock' );

$arSecrionFilter = null;
$arParams[ 'SECTION_FILTER' ] ? $arSecrionFilter = $arParams[ 'SECTION_FILTER' ] : array();
//Возможно, тут нужна другая
// логика, например предусмотреть обязательные поля

$arSectionSelect = null;
$arParams[ 'SECTION_SELECT' ] ? $arSectionSelect = $arParams[ 'SECTION_SELECT' ] : array();
//Возможно, тут нужна другая
// логика, например предусмотреть обязательные поля

$resSections = CIBlockSection::GetTreeList(
	$arSecrionFilter,
	$arSectionSelect//В этом массиве обязательно должен быть SECTION_ID
);
$arSectionsCatalog = array();
while( $arSection = $resSections->Fetch() ) {
	$arSectionsCatalog[ $arSection[ 'ID' ] ] = $arSection;//Получаем справочник секций
}

//Возможно, тут следует предусмотреть возможность отключения через параметры
$resElements = CIBlockElement::GetList(
	array(),
	array(),
	false,
	false,
	array(
		'SECTION_ID'
	)
);

while( $arElement = $resElements->Fetch() ) {

	/*Следующий код записывает элементы в справочник разделов в соответствии с ID раздела-родителя.
	если элемент находится в корне(не имеет раздела-родителя), то этот элемент записывается в
	$arResult[ 'SECTION_CONTENT' ][ 'ELEMENTS' ] */
	if( $arElement[ 'SECTION_ID' ] ) {
		$arSectionsCatalog[ $arElement[ 'SECTION_ID' ] ][ 'ELEMENTS' ][ $arElement[ 'ID' ] ] = $arElement;
	}
	else {
		$arResult[ 'SECTION_CONTENT' ][ 'ELEMENTS' ][ 'ID' ] = $arElement;
	}
}

foreach( $arSectionsCatalog as $id => $SectionArray ) {//Заполнение
	if( $SectionArray[ 'DEPTH_LEVEL' ] == 1 ) {
		$arResult[ 'SECTIONS_CONTENT' ][ $SectionArray[ 'ID' ] ] = $SectionArray;
		unset( $arSectionsCatalog[ "ID" ] );//Чтобы в дальнейшем было легче искать
	}
}

function SectionsTreeSort( $sectionID ) {
	global $arSectionsCatalog;
	$resultArray = array();
	if( is_array( $arSectionsCatalog ) ) {
		foreach( $arSectionsCatalog as $key => $catalog ) {
			if( $catalog[ 'SECTION_ID' ] == $sectionID ) {
				$resultArray[ $catalog[ 'ID' ] ] = $catalog;
				$resultArray[ $catalog[ 'ID' ] ][ 'SUBSECTIONS' ] = SectionsTreeSort( $catalog[ 'ID' ] );
			}
		}
	}

	return $resultArray;
}

foreach( $arResult[ 'SECTION_CONTENT' ] as $key => $section ) {
	$section[ 'SUBSECTIONS' ] = SectionsTreeSort( $section[ 'ID' ] );
}




