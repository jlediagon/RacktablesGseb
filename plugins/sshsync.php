<?php

/*
   This plug-in adds tab 'SSH sync' to the Object page.
   It is used to search and add ports for a switch with an ssh connection
*/

$tab['object']['sshsync'] = 'SSH Sync';
$tabhandler['object']['sshsync'] = 'sshsync_tabhandler';
$trigger['object']['sshsync'] = 'sshsync_tabtrigger';

$ophandler['object']['sshsync']['ajax'] = 'sshsync_opajax';
?>
