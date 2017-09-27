<?php 

	require_once "UgonaHttpHelper.php";
	require_once "SimpleHtmlDom.php";
	require_once "ExelHelper.php";

	class TutorialsParser
	{
		private $siteUrl = "https://www.ugona.net";
		private $ugonaHttpHelper;
		private $mainUrl = "https://www.ugona.net";


		public function parserTutorials()
		{
			$exelHelper = new ExelHelper();
			$exelHelper->createColumnsNames();
			$mainPageHtml = $this->ugonaHttpHelper->getMainPageHtml();

			$mainCategories = $this->parseMainCategories($mainPageHtml);


			foreach($mainCategories as $singleMainCategory)
			{
				echo "Current category: " . $singleMainCategory["name"] . PHP_EOL;

				$singleMainCategoryHtml = $this->ugonaHttpHelper->getCategoryHtml($singleMainCategory["url"]);

				if($this->categoryHaveSeveralPages($singleMainCategoryHtml))
				{
					echo "This category have several pages" . PHP_EOL;

					$allCategoryPagesHtml = array();
					$allCategoryPagesHtml[] = $singleMainCategoryHtml;
					$allCategoryLinks = $this->getAllLinksToMultiPageCategory($singleMainCategoryHtml);
						
					$parsedAllCategoryPagesHtml = $this->ugonaHttpHelper->getAllHtmlFiles($allCategoryLinks);

					foreach($parsedAllCategoryPagesHtml as $singleParsedCategoryPageHtml)
					{
						$allCategoryPagesHtml[] = $singleParsedCategoryPageHtml["html"];
					}

					$singleCategoryTutorials = [];

					foreach($allCategoryPagesHtml as $singleCategoryPageHtml)
					{
						$singleCategoryPageParsedTutorials = $this->parseSingleCategoryTutorials($singleCategoryPageHtml);
						foreach($singleCategoryPageParsedTutorials as $singleCategoryPageParsedTutorial)
						{
							$singleCategoryTutorials[] = $singleCategoryPageParsedTutorial;							
						}
					}
				}
				else
				{
					echo "This category have only one page" . PHP_EOL;
					$singleCategoryTutorials = $this->parseSingleCategoryTutorials($singleMainCategoryHtml);
				}

				
				foreach($singleCategoryTutorials as $singleCategoryTutorial)
				{
					$exelHelper->addRow(array(
						"category" => $singleMainCategory["name"],
						"tutorialName" => $singleCategoryTutorial["tutorialName"],
						"tutorialDescription" => $singleCategoryTutorial["tutorialDescription"],
						"file" => $singleCategoryTutorial["tutorialDocumentUrl"]
					));
				}

				$exelHelper->save("CreatedTable.xlsx");
				exit;
			}

		}
		public function getAllLinksToMultiPageCategory($html)
		{
			$allLinksFromMultipageCategory = array();
			$html = str_get_html($html);

			foreach($html->find("#top li a[href]") as $singleCategoryPageLink)
			{
				$allLinksFromMultipageCategory[] = $this->mainUrl . $singleCategoryPageLink->href;
			}

			return $allLinksFromMultipageCategory;
		}

		public function categoryHaveSeveralPages($html)
		{
			$html = str_get_html($html);

			if(count($html->find("#top > li")))
			{
				return true;
			}
			else
			{
				return false;
			}

		}

		public function parseSingleCategoryTutorials($html)
		{
			$singleCategoryTutorials = array();
			$html = str_get_html($html);

			foreach($html->find(".price tr") as $singleTutorialTr)
			{
				if($singleTutorialTr->find("td", 0))
				{
					$singleTutorial = array();
					$singleTutorial["tutorialName"] = $singleTutorialTr->find("td", 0)->find("h5 a", 0)->innertext;
					$singleTutorial["tutorialDocumentUrl"] = $singleTutorialTr->find("td", 0)->find("h5 a", 0)->href;

					if($singleTutorial["tutorialDescription"] = $singleTutorialTr->find("td", 0)->find("p", 0))
					{
						$singleTutorial["tutorialDescription"] = $singleTutorialTr->find("td", 0)->find("p", 0)->innertext;
					}

					//var_dump($singleTutorial);

					$singleCategoryTutorials[] = $singleTutorial;
				}
			}

			return $singleCategoryTutorials;
		}
		public function parseMainCategories($html)
		{
			$mainCategoriesList = array();
			$html = str_get_html($html);

			foreach($html->find(".brands li a") as $singleBrandLink)
			{
				$singleBrand = array();

				$singleBrand["name"] = $singleBrandLink->innertext; 
				$singleBrand["url"] = $singleBrandLink->href; 

				$mainCategoriesList[] = $singleBrand;
			}


			return $mainCategoriesList;
		}

		public static function getInstance()
		{
			$instance = null;
			if($instance == null)
			{
				$instance = new TutorialsParser();
			}  

			return $instance;
		}
		private function __construct()
		{
			$this->ugonaHttpHelper = UgonaHttpHelper::getInstance();
		}
	}



?>