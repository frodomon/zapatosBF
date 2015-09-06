<?php
 
class ThemeOptions_ExtraConfig_InstallController extends Mage_Adminhtml_Controller_Action {
    
        
    public function installAction() {
        // variables
        $cmsModel = Mage::getModel('cms/block');
        $msg_success = '';
        $msg_errors = '';
        $success = 0;
        $errors = 0;
        $errorTexts = array();
        
        // load blocks from XML file (media/etheme/Dummy.xml)
        $blocks = $this->getBlocksXmlSimple();
        
        foreach($blocks as $block) {
            // Prepare Block array
            $toInstall = array(
                'title' => $block['title'],
                'identifier' => $block['id'],                   
                'content' => $block['content'],
                'is_active' => 1,                   
                'stores' => array(0)
            );
            
            try {
                // Try to add Block to DB
                $cmsModel->setData($toInstall)->save();
                
                $success++;
            } catch (Exception $e) {
                // If block already exists we have error
                $errors++;
                $errorTexts[] = $e->getMessage();
            }
            
        }
        if($success > 0)
            $msg_success = '<li class="success-msg"><ul><li><span>Successfully installed '.$success.' blocks</span></li></ul></li>';

        if($errors > 0) {
            $msg_errors = '<li class="error-msg"><ul><li><span>Error with '.$errors.' blocks installation</span></li></ul></li>';
            foreach ($errorTexts as $err) {
                $msg_errors .= '<li class="error-msg"><ul><li><span>Error with '.$err.'</span></li></ul></li>';
            }
        }
        
    
        $this->loadLayout();
        
        
        $url = $this->getUrl('adminhtml/system_config'); 
        
        // Create Button HTML
        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Return to configuration')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();
        
        // Show info
        $block = $this->getLayout()
        ->createBlock('core/text', 'example-block')
        ->setText('<div id="messages"><ul class="messages">'.$msg_success.$msg_errors.'</ul></div><br>'.$html);

        $this->_addContent($block);

        $this->renderLayout();
    }   
    
    public function getBlocksXML() { 
        $xml = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'etheme/cms.xml';
        $reader = new DOMDocument(); 
        $reader->load($xml);  //Loads the Above XML 
        
        $i = 0; 
             
        foreach ($reader->getElementsByTagName('cmsblock') as $node) { 
        
            $res[$i]['id'] = $node->getElementsByTagName('id')->item(0)->nodeValue; 
            $res[$i]['title'] = $node->getElementsByTagName('title')->item(0)->nodeValue; 
            $res[$i]['content'] = $node->getElementsByTagName('content')->item(0)->nodeValue; 
    
            $i++; 
        } 
        
        return $res; 
    }  

    /*
    For the cases when DOMDocument isn't installed
    */
    public function getBlocksXmlSimple() {

        $xml = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'etheme/cms.xml';
        $doc = simplexml_load_file($xml,'SimpleXMLElement', LIBXML_NOCDATA);
        $i = 0; 
             
        foreach ($doc->cmsblocks->cmsblock as $node) { 
           
            $res[$i]['id'] = (string) $node->id; 
            $res[$i]['title'] = (string) $node->title; 
            $res[$i]['content'] = (string) $node->content; 
            $i++; 
        } 
        
        return $res; 
    }

    // public function indexAction() {

    //     $this->loadLayout();
    //     $url = $this->getUrl('blanco_backend/install/install'); 
        
    //     // Create Button HTML
    //     $html = $this->getLayout()->createBlock('adminhtml/widget_button')
    //                 ->setType('button')
    //                 ->setClass('scalable')
    //                 ->setLabel('Install blocks')
    //                 ->setOnClick("setLocation('$url')")
    //                 ->toHtml();
        
    //     // Show info
    //     $block = $this->getLayout()
    //     ->createBlock('core/text', 'example-block')
    //     ->setText('<div id="messages"><ul class="messages">'.$msg_success.$msg_errors.'</ul></div><br>'.$html);
    //     $this->_addContent($block);
    //     $this->renderLayout();
    // }
}