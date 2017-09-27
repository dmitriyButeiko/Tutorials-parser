<?php 

	require_once "HttpHelper.php";

	class UgonaHttpHelper
	{
		private $httpHelper;
		private $mainUrl = "https://www.ugona.net";
		public function getMainPageHtml()
		{
			return $this->httpHelper->getHtml($this->mainUrl . "/manuals.html");
		}

		public function getCategoryHtml($categoryUrl)
		{
			return $this->httpHelper->getHtml($this->mainUrl . $categoryUrl);
		}

		public function getAllHtmlFiles($urls)
		{
			return $this->httpHelper->multiRequest($urls);
		}

		public static function getInstance()
		{
			$instance = null;
			if($instance == null)
			{
				$instance = new UgonaHttpHelper();
			}  

			return $instance;
		}
		private function __construct()
		{
			$this->httpHelper = HttpHelper::getHelper();
		}
	}


?>