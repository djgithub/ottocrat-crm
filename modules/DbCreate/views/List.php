<?php
class DbCreate_List_View extends Ottocrat_Index_View {

        public function process(Ottocrat_Request $request) {
                $viewer = $this->getViewer($request);
				  global $adb; print_r($viewer);
				 
        }

}
?>