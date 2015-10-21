<?php
class UpgradeBilling_List_View extends Ottocrat_Index_View {

        public function process(Ottocrat_Request $request) {
                $viewer = $this->getViewer($request);
                $viewer->view('List.tpl', $request->getModule());
        }

}
?>