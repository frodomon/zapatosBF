<?php

if(!defined('MagicScrollModuleCoreClassLoaded')) {

    define('MagicScrollModuleCoreClassLoaded', true);

    require_once(dirname(__FILE__) . '/magictoolbox.params.class.php');

    /**
     * MagicScrollModuleCoreClass
     *
     */
    class MagicScrollModuleCoreClass {

        /**
         * MagicToolboxParamsClass class
         *
         * @var   MagicToolboxParamsClass
         *
         */
        var $params;

        /**
         * Tool type
         *
         * @var   string
         *
         */
        var $type = 'category';

        /**
         * Constructor
         *
         * @return void
         */
        function MagicScrollModuleCoreClass() {
            $this->params = new MagicToolboxParamsClass();
            $this->loadDefaults();
            $this->params->setMapping(array(
                'width' => array('0' => 'auto'),
                'height' => array('0' => 'auto'),
                'item-width' => array('0' => 'auto'),
                'item-height' => array('0' => 'auto'),
                //NOTE: what is it for?
            ));
        }

        /**
         * Metod to get headers string
         *
         * @param string $jsPath  Path to JS file
         * @param string $cssPath Path to CSS file
         *
         * @return string
         */
        function getHeadersTemplate($jsPath = '', $cssPath = null) {
            //to prevent multiple displaying of headers
            if(!defined('MagicScrollModuleHeaders')) {
                define('MagicScrollModuleHeaders', true);
            } else {
                return '';
            }
            if($cssPath == null) {
                $cssPath = $jsPath;
            }
            $headers = array();
            // add module version
            $headers[] = '<!-- Magic Zoom Magento module version v4.9.25 [v1.4.21:v4.5.30] -->';
            // add style link
            $headers[] = '<link type="text/css" href="' . $cssPath . '/magicscroll.css" rel="stylesheet" media="screen" />';
            // add script link
            $headers[] = '<script type="text/javascript" src="' . $jsPath . '/magicscroll.js"></script>';
            // add options
            $headers[] = $this->getOptionsTemplate();
            return "\r\n" . implode("\r\n", $headers) . "\r\n";
        }

        /**
         * Metod to get options string
         *
         * @param mixed $id Extra options ID
         *
         * @return string
         */
        function getOptionsTemplate($id = null) {
            $addition = '';
            if($this->params->paramExists('item-tag')) {
                $addition .= "\n\t\t'item-tag':'" . $this->params->getValue('item-tag') . "',";
            }
            //NOTICE: scope == 'MagicScroll'
            return "<script type=\"text/javascript\">\n\tMagicScroll.".($id == null?"options":"extraOptions.".$id)." = {{$addition}\n\t\t".$this->params->serialize(true, ",\n\t\t")."\n\t}\n</script>";
        }

        /**
         * Metod to get MagicScroll HTML
         *
         * @param array $itemsData MagicScroll data
         * @param array $params Additional params
         *
         * @return string
         */
        function getMainTemplate($itemsData, $params = array()) {

            $id = '';
            $width = '';
            $height = '';

            $html = array();

            extract($params);

            if(empty($width)) {
                $width = '';
            } else {
                $width = " width=\"{$width}\"";
            }
            if(empty($height)) {
                $height = '';
            } else {
                $height = " height=\"{$height}\"";
            }

            if(empty($id)) {
                $id = '';
            } else {
                // add personal options
                //NOTICE: what is it for?
                $html[] = $this->getOptionsTemplate($id);
                $id = ' id="' . addslashes($id) . '"';
            }

            // add div with tool className
            $additionalClasses = array(
                'default' => '',
                'with-borders' => 'msborder'
            );
            $additionalClass = $additionalClasses[$this->params->getValue('scroll-style')];
            if(!empty($additionalClass)) {
                $additionalClass = ' ' . $additionalClass;
            }
            $html[] = '<div' . $id . ' class="MagicScroll' . $additionalClass . '"' . $width . $height . '>';

            // add items
            foreach($itemsData as $item) {

                $img = '';
                $thumb = '';
                $link = '';
                $target = '';
                $alt = '';
                $title = '';
                $description = '';
                $width = '';
                $height = '';

                extract($item);

                // check big image
                if(empty($img)) {
                    $img = '';
                }

                //NOTE: remove this?
                if(isset($medium)) {
                    $thumb = $medium;
                }

                // check thumbnail
                if(!empty($img) || empty($thumb)) {
                    $thumb = $img;
                }

                // check item link
                if(empty($link)) {
                    $link = '';
                } else {
                    // check target
                    if(empty($target)) {
                        $target = '';
                    } else {
                        $target = ' target="' . $target . '"';
                    }
                    $link = $target . ' href="' . addslashes($link) . '"';
                }

                // check item alt tag
                if(empty($alt)) {
                    $alt = '';
                } else {
                    $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
                }

                // check title
                if(empty($title)) {
                    $title = '';
                } else {
                    $title = htmlspecialchars(htmlspecialchars_decode($title, ENT_QUOTES));
                    if(empty($alt)) {
                        $alt = $title;
                    }
                    if($this->params->checkValue('show-image-title', 'No')) {
                        $title = '';
                    }
                }

                // check description
                if(empty($description)) {
                    $description = '';
                } else {
                    //$description = preg_replace("/<(\/?)a([^>]*)>/is", "[$1a$2]", $description);
                    $description = "<span>{$description}</span>";
                }

                if(empty($width)) {
                    $width = '';
                } else {
                    $width = " width=\"{$width}\"";
                }
                if(empty($height)) {
                    $height = '';
                } else {
                    $height = " height=\"{$height}\"";
                }

                // add item
                $html[] = "<a{$link}><img{$width}{$height} src=\"{$thumb}\" alt=\"{$alt}\" />{$title}{$description}</a>";
            }

            // close core div
            $html[] = '</div>';

            // create HTML string
            $html = implode('', $html);

            // return result
            return $html;
        }

        /**
         * Metod to load defaults options
         *
         * @return void
         */
        function loadDefaults() {
            $params = array("enable-effect"=>array("id"=>"enable-effect","group"=>"General","order"=>"10","default"=>"Yes","label"=>"Enable Magic Zoom™","type"=>"array","subType"=>"select","values"=>array("Yes","No"),"scope"=>"profile"),"template"=>array("id"=>"template","group"=>"General","order"=>"20","default"=>"bottom","label"=>"Thumbnail layout","type"=>"array","subType"=>"select","values"=>array("original","bottom","left","right","top"),"scope"=>"profile"),"include-headers-on-all-pages"=>array("id"=>"include-headers-on-all-pages","group"=>"General","order"=>"21","default"=>"No","label"=>"Include headers on all pages","description"=>"To be able to apply an effect on any page","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"profile"),"magicscroll"=>array("id"=>"magicscroll","group"=>"General","order"=>"22","default"=>"No","label"=>"Scroll thumbnails","description"=>"<br/>Powered by the versatile <a target=\"_blank\" href=\"http://www.magictoolbox.com/magicscroll/examples/\">Magic Scroll</a>™. Normally $49, yours is discounted to $39. <a target=\"_blank\" href=\"http://www.magictoolbox.com/magiczoom/magicscroll/\">Buy a license</a> and upload magicscroll.js to your server. <a target=\"_blank\" href=\"http://www.magictoolbox.com/contact/\">Contact us</a> for help.","type"=>"array","subType"=>"select","values"=>array("Yes","No"),"scope"=>"profile"),"thumb-max-width"=>array("id"=>"thumb-max-width","group"=>"Positioning and Geometry","order"=>"10","default"=>"250","label"=>"Maximum width of thumbnail (in pixels)","type"=>"num","scope"=>"profile"),"thumb-max-height"=>array("id"=>"thumb-max-height","group"=>"Positioning and Geometry","order"=>"11","default"=>"250","label"=>"Maximum height of thumbnail (in pixels)","type"=>"num","scope"=>"profile"),"square-images"=>array("id"=>"square-images","group"=>"Positioning and Geometry","order"=>"40","default"=>"No","label"=>"Always create square images","description"=>"","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"profile"),"zoom-width"=>array("id"=>"zoom-width","group"=>"Positioning and Geometry","order"=>"140","default"=>"300","label"=>"Width of zoom window","description"=>"pixels or percentage, e.g. 400 or 100%","type"=>"text","scope"=>"tool"),"zoom-height"=>array("id"=>"zoom-height","group"=>"Positioning and Geometry","order"=>"150","default"=>"300","label"=>"Height of zoom window","description"=>"pixels or percentage, e.g. 400 or 100%","type"=>"text","scope"=>"tool"),"zoom-position"=>array("id"=>"zoom-position","group"=>"Positioning and Geometry","order"=>"160","default"=>"right","label"=>"Position of zoom window relative to small image","type"=>"array","subType"=>"select","values"=>array("top","right","bottom","left","inner"),"scope"=>"tool"),"zoom-align"=>array("id"=>"zoom-align","advanced"=>"1","group"=>"Positioning and Geometry","order"=>"161","default"=>"top","label"=>"Align zoom window to any edge of your main image","type"=>"array","subType"=>"select","values"=>array("right","left","top","bottom","center"),"scope"=>"tool"),"zoom-distance"=>array("id"=>"zoom-distance","advanced"=>"1","group"=>"Positioning and Geometry","order"=>"170","default"=>"15","label"=>"Distance between small image and zoom window (in pixels)","type"=>"num","scope"=>"tool"),"opacity"=>array("id"=>"opacity","group"=>"Effects","order"=>"270","default"=>"50","label"=>"Hover area opacity (0-100)","description"=>"0 = transparent, 100 = solid color","type"=>"num","scope"=>"tool"),"opacity-reverse"=>array("id"=>"opacity-reverse","group"=>"Effects","order"=>"280","default"=>"No","label"=>"Add opacity outside mouse box","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"zoom-fade"=>array("id"=>"zoom-fade","group"=>"Effects","order"=>"290","default"=>"Yes","label"=>"Use fade effect when zoom window appears/disappears","type"=>"array","subType"=>"select","values"=>array("Yes","No"),"scope"=>"tool"),"zoom-window-effect"=>array("id"=>"zoom-window-effect","group"=>"Effects","order"=>"291","default"=>"shadow","label"=>"Add shadow or glow on zoom window","type"=>"array","subType"=>"select","values"=>array("shadow","glow","false"),"scope"=>"tool"),"zoom-fade-in-speed"=>array("id"=>"zoom-fade-in-speed","advanced"=>"1","group"=>"Effects","order"=>"300","default"=>"200","label"=>"Fade-in duration when zoom window appears (milliseconds)","description"=>"e.g. 200 = 0.2 seconds","type"=>"num","scope"=>"tool"),"zoom-fade-out-speed"=>array("id"=>"zoom-fade-out-speed","advanced"=>"1","group"=>"Effects","order"=>"310","default"=>"200","label"=>"Fade-out duration when zoom window disappears (milliseconds)","description"=>"e.g. 200 = 0.2 seconds","type"=>"num","scope"=>"tool"),"fps"=>array("id"=>"fps","advanced"=>"1","group"=>"Effects","order"=>"320","default"=>"25","label"=>"Frames per second for zoom effect","type"=>"num","scope"=>"tool"),"smoothing"=>array("id"=>"smoothing","group"=>"Effects","order"=>"330","default"=>"Yes","label"=>"Enable smooth zoom movement","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"smoothing-speed"=>array("id"=>"smoothing-speed","advanced"=>"1","group"=>"Effects","order"=>"340","default"=>"40","label"=>"Speed of smoothing (1-99)","type"=>"num","scope"=>"tool"),"selector-max-width"=>array("id"=>"selector-max-width","group"=>"Multiple images","order"=>"10","default"=>"56","label"=>"Maximum width of additional thumbnails (in pixels)","type"=>"num","scope"=>"profile"),"selector-max-height"=>array("id"=>"selector-max-height","group"=>"Multiple images","order"=>"11","default"=>"56","label"=>"Maximum height of additional thumbnails (in pixels)","type"=>"num","scope"=>"profile"),"show-selectors-on-category-page"=>array("id"=>"show-selectors-on-category-page","group"=>"Multiple images","order"=>"20","default"=>"No","label"=>"Show selectors on category page","type"=>"array","subType"=>"select","values"=>array("Yes","No"),"scope"=>"profile"),"use-individual-titles"=>array("id"=>"use-individual-titles","group"=>"Multiple images","order"=>"40","default"=>"Yes","label"=>"Use individual image titles for additional images?","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"profile"),"selectors-margin"=>array("id"=>"selectors-margin","group"=>"Multiple images","order"=>"40","default"=>"5","label"=>"Margin between selectors and main image (in pixels)","type"=>"num","scope"=>"profile"),"selectors-change"=>array("id"=>"selectors-change","group"=>"Multiple images","order"=>"110","default"=>"click","label"=>"Method to switch between multiple images","type"=>"array","subType"=>"select","values"=>array("click","mouseover"),"scope"=>"tool"),"selectors-class"=>array("id"=>"selectors-class","group"=>"Multiple images","order"=>"111","default"=>"","label"=>"Highlight the current thumbnail using a CSS class","type"=>"text","scope"=>"tool"),"preload-selectors-small"=>array("id"=>"preload-selectors-small","advanced"=>"1","group"=>"Multiple images","order"=>"120","default"=>"Yes","label"=>"Preload small images","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"preload-selectors-big"=>array("id"=>"preload-selectors-big","group"=>"Multiple images","order"=>"130","default"=>"No","label"=>"Preload large images","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"selectors-effect"=>array("id"=>"selectors-effect","group"=>"Multiple images","order"=>"140","default"=>"fade","label"=>"Effect when selecting thumbnail images","type"=>"array","subType"=>"select","values"=>array("dissolve","fade","pounce","disable"),"scope"=>"tool"),"selectors-effect-speed"=>array("id"=>"selectors-effect-speed","advanced"=>"1","group"=>"Multiple images","order"=>"150","default"=>"400","label"=>"Duration thumbnails change (milliseconds)","description"=>"e.g. 400 = 0.4 seconds","type"=>"num","scope"=>"tool"),"selectors-mouseover-delay"=>array("id"=>"selectors-mouseover-delay","advanced"=>"1","group"=>"Multiple images","order"=>"160","default"=>"60","label"=>"Delay before switching thumbnails (milliseconds)","description"=>"e.g. 200 = 0.2 seconds","type"=>"num","scope"=>"tool"),"initialize-on"=>array("id"=>"initialize-on","group"=>"Initialization","order"=>"70","default"=>"load","label"=>"When to download large image","type"=>"array","subType"=>"radio","values"=>array("load","click","mouseover"),"scope"=>"tool"),"click-to-activate"=>array("id"=>"click-to-activate","advanced"=>"1","group"=>"Initialization","order"=>"80","default"=>"No","label"=>"Click to show the zoom","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"click-to-deactivate"=>array("id"=>"click-to-deactivate","advanced"=>"1","group"=>"Initialization","order"=>"81","default"=>"No","label"=>"Click to deactivate zoom window","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"show-loading"=>array("id"=>"show-loading","group"=>"Initialization","order"=>"90","default"=>"Yes","label"=>"Loading message appears when zoom tool begins","type"=>"array","subType"=>"select","values"=>array("Yes","No"),"scope"=>"tool"),"loading-msg"=>array("id"=>"loading-msg","group"=>"Initialization","order"=>"100","default"=>"Loading zoom...","label"=>"Text to appear as Loading message","type"=>"text","scope"=>"tool"),"loading-opacity"=>array("id"=>"loading-opacity","advanced"=>"1","group"=>"Initialization","order"=>"110","default"=>"75","label"=>"Loading message opacity (0-100)","description"=>"0 = transparent, 100 = solid color","type"=>"num","scope"=>"tool"),"loading-position-x"=>array("id"=>"loading-position-x","advanced"=>"1","group"=>"Initialization","order"=>"120","default"=>"-1","label"=>"Horizontal (X-axis) position of Loading message","description"=>"-1 = center","type"=>"num","scope"=>"tool"),"loading-position-y"=>array("id"=>"loading-position-y","advanced"=>"1","group"=>"Initialization","order"=>"130","default"=>"-1","label"=>"Vertical (Y-axis) position of Loading message","description"=>"-1 = center","type"=>"num","scope"=>"tool"),"entire-image"=>array("id"=>"entire-image","group"=>"Initialization","order"=>"140","default"=>"No","label"=>"Show entire large image on hover","description"=>"default set to show only part of large image in zoom window","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"show-title"=>array("id"=>"show-title","group"=>"Title and Caption","order"=>"10","default"=>"top","label"=>"Show image title in zoom window","type"=>"array","subType"=>"select","values"=>array("top","bottom","disable"),"scope"=>"tool"),"link-to-product-page"=>array("id"=>"link-to-product-page","group"=>"Miscellaneous","order"=>"30","default"=>"Yes","label"=>"Link enlarged image to the product page","type"=>"array","subType"=>"select","values"=>array("Yes","No"),"scope"=>"profile"),"option-associated-with-images"=>array("id"=>"option-associated-with-images","group"=>"Miscellaneous","order"=>"40","default"=>"color","label"=>"Options names associated with images separated by commas (e.g 'Color,Size')","description"=>"You should named all product images associated with option values. e.g If option values is 'red', 'blue' and 'white' then you should have 3 images with labels 'red', 'blue' and 'white'","type"=>"text","scope"=>"profile"),"show-associated-product-images"=>array("id"=>"show-associated-product-images","group"=>"Miscellaneous","order"=>"41","default"=>"Yes","label"=>"Show associated product's images","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"profile"),"load-associated-product-images"=>array("id"=>"load-associated-product-images","group"=>"Miscellaneous","order"=>"42","default"=>"when option is selected","label"=>"Load associated product's images","type"=>"array","subType"=>"radio","values"=>array("when option is selected","within a gallery"),"scope"=>"profile"),"ignore-magento-css"=>array("id"=>"ignore-magento-css","group"=>"Miscellaneous","order"=>"50","default"=>"No","label"=>"Ignore magento CSS width/height styles for additional images","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"profile"),"show-message"=>array("id"=>"show-message","group"=>"Miscellaneous","order"=>"370","default"=>"Yes","label"=>"Show message under image?","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"message"=>array("id"=>"message","group"=>"Miscellaneous","order"=>"380","default"=>"Move your mouse over image","label"=>"Enter message to appear under images","type"=>"text"),"right-click"=>array("id"=>"right-click","group"=>"Miscellaneous","order"=>"385","default"=>"No","label"=>"Enable right-click menu on image","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"disable-zoom"=>array("id"=>"disable-zoom","group"=>"Zoom mode","order"=>"9","default"=>"No","label"=>"Disable zoom effect","description"=>"e.g. swap images only","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"always-show-zoom"=>array("id"=>"always-show-zoom","group"=>"Zoom mode","order"=>"10","default"=>"No","label"=>"Make zoom window always visible","description"=>"This will automatically happen in drag-mode","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"drag-mode"=>array("id"=>"drag-mode","group"=>"Zoom mode","order"=>"20","default"=>"No","label"=>"Click and drag to move the zoom","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"move-on-click"=>array("id"=>"move-on-click","advanced"=>"1","group"=>"Zoom mode","order"=>"30","default"=>"Yes","label"=>"Click alone will also move zoom (drag mode only)","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"x"=>array("id"=>"x","advanced"=>"1","group"=>"Zoom mode","order"=>"40","default"=>"-1","label"=>"Initial horizontal (X-axis) zoom position (in drag mode)","description"=>"-1 = center","type"=>"num","scope"=>"tool"),"y"=>array("id"=>"y","advanced"=>"1","group"=>"Zoom mode","order"=>"50","default"=>"-1","label"=>"Initial vertical (Y-axis) zoom position (in drag mode)","description"=>"-1 = center","type"=>"num","scope"=>"tool"),"preserve-position"=>array("id"=>"preserve-position","advanced"=>"1","group"=>"Zoom mode","order"=>"60","default"=>"No","label"=>"Position of zoom can be remembered for multiple images and drag mode","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"fit-zoom-window"=>array("id"=>"fit-zoom-window","advanced"=>"1","group"=>"Zoom mode","order"=>"70","default"=>"Yes","label"=>"Resize zoom window if big image is smaller than zoom window","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"hint"=>array("id"=>"hint","group"=>"Hint","order"=>"10","default"=>"Yes","label"=>"Display hint to suggest image is zoomable","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"tool"),"hint-text"=>array("id"=>"hint-text","group"=>"Hint","order"=>"15","default"=>"Zoom","label"=>"Hint text","type"=>"text","scope"=>"tool"),"hint-position"=>array("id"=>"hint-position","advanced"=>"1","group"=>"Hint","order"=>"20","default"=>"top left","label"=>"Hint position","type"=>"array","subType"=>"select","values"=>array("top left","top right","top center","bottom left","bottom right","bottom center"),"scope"=>"tool"),"hint-opacity"=>array("id"=>"hint-opacity","advanced"=>"1","group"=>"Hint","order"=>"25","default"=>"75","label"=>"Hint opacity (0-100)","description"=>"0 = transparent, 100 = solid color","type"=>"num","scope"=>"tool"),"scroll-style"=>array("id"=>"scroll-style","group"=>"Scroll","order"=>"5","default"=>"default","label"=>"Style","type"=>"array","subType"=>"select","values"=>array("default","with-borders"),"scope"=>"profile"),"show-image-title"=>array("id"=>"show-image-title","group"=>"Scroll","order"=>"6","default"=>"Yes","label"=>"Show image title under images","type"=>"array","subType"=>"radio","values"=>array("Yes","No")),"loop"=>array("id"=>"loop","group"=>"Scroll","order"=>"10","default"=>"continue","label"=>"Restart scroll after last image","description"=>"After last item, continue or go back to beginning","type"=>"array","subType"=>"radio","values"=>array("continue","restart"),"scope"=>"MagicScroll"),"speed"=>array("id"=>"speed","group"=>"Scroll","order"=>"20","default"=>"0","label"=>"Duration item displayed (milliseconds)","description"=>"0 = manual scroll; 5000 = 5 seconds","type"=>"num","scope"=>"MagicScroll"),"width"=>array("id"=>"width","group"=>"Scroll","order"=>"30","default"=>"0","label"=>"Scroll width (pixels)","description"=>"0 - auto","type"=>"num","scope"=>"MagicScroll"),"height"=>array("id"=>"height","group"=>"Scroll","order"=>"40","default"=>"0","label"=>"Scroll height (pixels)","description"=>"0 - auto","type"=>"num","scope"=>"MagicScroll"),"item-width"=>array("id"=>"item-width","group"=>"Scroll","order"=>"50","default"=>"0","label"=>"Width of entire scroll area (pixels)","description"=>"0 - auto","type"=>"num","scope"=>"MagicScroll"),"item-height"=>array("id"=>"item-height","group"=>"Scroll","order"=>"60","default"=>"0","label"=>"Height of entire scroll area (pixels)","description"=>"0 - auto","type"=>"num","scope"=>"MagicScroll"),"step"=>array("id"=>"step","group"=>"Scroll","order"=>"70","default"=>"3","label"=>"Number of items moved on each scroll","type"=>"num","scope"=>"MagicScroll"),"items"=>array("id"=>"items","group"=>"Scroll","order"=>"80","default"=>"3","label"=>"Items to show","description"=>"0 - manual","type"=>"num","scope"=>"MagicScroll"),"arrows"=>array("id"=>"arrows","group"=>"Scroll Arrows","order"=>"10","default"=>"outside","label"=>"Show arrows","label"=>"Where arrows should be placed","type"=>"array","subType"=>"radio","values"=>array("outside","inside","false"),"scope"=>"MagicScroll"),"arrows-opacity"=>array("id"=>"arrows-opacity","advanced"=>"1","group"=>"Scroll Arrows","order"=>"20","default"=>"60","label"=>"Arrows opacity (0-100)","description"=>"0 = transparent, 100 = solid color","type"=>"num","scope"=>"MagicScroll"),"arrows-hover-opacity"=>array("id"=>"arrows-hover-opacity","advanced"=>"1","group"=>"Scroll Arrows","order"=>"30","default"=>"100","label"=>"Arrows opacity on mouse over (0-100)","description"=>"0 = transparent, 100 = solid color","type"=>"num","scope"=>"MagicScroll"),"slider-size"=>array("id"=>"slider-size","group"=>"Scroll Slider","order"=>"10","default"=>"10%","label"=>"Slider size (numeric or percent)","type"=>"text","scope"=>"MagicScroll"),"slider"=>array("id"=>"slider","group"=>"Scroll Slider","order"=>"20","default"=>"false","label"=>"Slider postion","type"=>"array","subType"=>"select","values"=>array("top","right","bottom","left","false"),"scope"=>"MagicScroll"),"direction"=>array("id"=>"direction","group"=>"Scroll effect","order"=>"10","default"=>"right","value"=>"bottom","label"=>"Scroll direction","description"=>"bottom = scroll moves from bottom to top","type"=>"array","subType"=>"select","values"=>array("top","right","bottom","left"),"scope"=>"MagicScroll"),"duration"=>array("id"=>"duration","group"=>"Scroll effect","order"=>"20","default"=>"1000","label"=>"Duration of each scroll (milliseconds)","description"=>"e.g. 0 = instant; 1000 = 1 second","type"=>"num","scope"=>"MagicScroll"));
            $this->params->appendParams($params);
        }
    }

}

?>
