@echo off

::set code page
@chcp 1251 > nul

if "%1"=="" goto help
   
goto %1
    :help
        php dbmigrator.php help
        goto end_switch
    :init
        php dbmigrator.php init %2
        goto end_switch
    :create
        php dbmigrator.php create %2
        goto end_switch
    :commit
        php dbmigrator.php commit %2 %3
        goto end_switch
    :delete
        php dbmigrator.php delete %2 %3
        goto end_switch
    :goto
        php dbmigrator.php goto %2 %3 %4
        goto end_switch
    :info
        php dbmigrator.php info %2
        goto end_switch
    :log
        php dbmigrator.php log %2
        goto end_switch
    :showdelta
        php dbmigrator.php showdelta %2 %3
        goto end_switch		
	  :empty
		php dbmigrator.php help
        goto end_switch
:end_switch

