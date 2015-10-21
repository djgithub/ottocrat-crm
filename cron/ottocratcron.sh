#*********************************************************************************
# The contents of this file are subject to the ottocrat CRM Public License Version 1.0
# ("License"); You may not use this file except in compliance with the License
# The Original Code is:  ottocrat CRM Open Source
# The Initial Developer of the Original Code is ottocrat.
# Portions created by ottocrat are Copyright (C) ottocrat.
# All Rights Reserved.
#
# ********************************************************************************

export OTTOCRATCRM_ROOTDIR=`dirname "$0"`/..
export USE_PHP=php

cd $OTTOCRATCRM_ROOTDIR
# TO RUN ALL CORN JOBS
$USE_PHP -f ottocratcron.php
