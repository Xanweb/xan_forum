<?php
namespace XanForum;

use PageTemplate;
use PageTheme;

class PageTemplateAreas
{
    public static function getAreas($pTemplateHandle)
    {
        $pTemplate = PageTemplate::getByHandle($pTemplateHandle);

        if (!is_object($pTemplate)) {
            return [];
        }

        $pt = PageTheme::getSiteTheme();
        $themeDirectory = $pt->getThemeDirectory();
        $templateFile = $pTemplate->getPageTemplateHandle() . '.php';
        if (!file_exists($themeDirectory . '/' . $templateFile)) {
            $templateFile = 'default.php';
        }

        return static::fetchAreas($templateFile, $themeDirectory);
    }

    private static function fetchAreas($file, $themeDirectory)
    {
        $fileConent = file_get_contents($themeDirectory . '/' . $file);
        $areaRegex = '/Area\\(("|\')([^)]*)("|\')\\);/';
        preg_match_all($areaRegex, $fileConent, $data);
        $areas = $data[2];
        unset($data);

        // Check for php includes
        preg_match_all('/inc\\(("|\')([^)]*)("|\')\\);/', $fileConent, $m);
        if (is_array($m[2])) {
            foreach ($m[2] as $includeFile) {
                $areas = $areas + static::fetchAreas($includeFile, $themeDirectory);
            }
        }

        return $areas;
    }
}
