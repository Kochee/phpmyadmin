<?php
/* $Id$ */


/**
 * Runs common work
 */
require('./db_details_common.php3');
$url_query .= '&amp;goto=db_details.php3';

/**
 * Database work
 */
if (isset($show_query) && $show_query == '1') {
    // This script has been called by read_dump.php3
    if (isset($sql_query_cpy)) {
        $query_to_display = $sql_query_cpy;
    }
    // Other cases
    else if (get_magic_quotes_gpc()) {
        $query_to_display = stripslashes($sql_query);
    }
    else {
        $query_to_display = $sql_query;
    }
} else {
    $query_to_display     = '';
}


/**
 * Gets informations about the database and, if it is empty, move to the
 * "db_details_structure.php3" script where table can be created
 */
$sub_part    = '';
require('./db_details_db_info.php3');
if ($num_tables == 0 && empty($db_query_force)) {
    $is_info = TRUE;
    include('./db_details_structure.php3');
    exit();
}

// loic1: defines wether file upload is available or not
$is_upload = (PMA_PHP_INT_VERSION >= 40000 && function_exists('ini_get'))
           ? ((strtolower(ini_get('file_uploads')) == 'on' || ini_get('file_uploads') == 1) && intval(ini_get('upload_max_filesize')))
           // loic1: php 3.0.15 and lower bug -> always enabled
           : (PMA_PHP_INT_VERSION < 30016 || intval(@get_cfg_var('upload_max_filesize')));
?>
<!-- Query box, sql file loader and bookmark support -->
<a name="querybox"></a>
<form method="post" action="read_dump.php3"<?php if ($is_upload) echo ' enctype="multipart/form-data"'; echo "\n"; ?>
    onsubmit="return checkSqlQuery(this)">
    <input type="hidden" name="is_js_confirmed" value="0" />
    <input type="hidden" name="lang" value="<?php echo $lang; ?>" />
    <input type="hidden" name="convcharset" value="<?php echo $convcharset; ?>" />
    <input type="hidden" name="server" value="<?php echo $server; ?>" />
    <input type="hidden" name="db" value="<?php echo $db; ?>" />
    <input type="hidden" name="pos" value="0" />
    <input type="hidden" name="goto" value="db_details.php3" />
    <input type="hidden" name="zero_rows" value="<?php echo htmlspecialchars($strSuccess); ?>" />
    <input type="hidden" name="prev_sql_query" value="<?php echo ((!empty($query_to_display)) ? urlencode($query_to_display) : ''); ?>" />
    <?php echo sprintf($strRunSQLQuery, $db) . ' ' . PMA_showMySQLDocu('Reference', 'SELECT'); ?>&nbsp;:<br />
    <div style="margin-bottom: 5px">
<textarea name="sql_query" cols="<?php echo $cfg['TextareaCols'] * 2; ?>" rows="<?php echo $cfg['TextareaRows']; ?>" wrap="virtual" dir="<?php echo $text_dir; ?>"
    onfocus="if (typeof(document.layers) == 'undefined' || typeof(textarea_selected) == 'undefined') {textarea_selected = 1; this.form.elements['sql_query'].select();}">
<?php echo ((!empty($query_to_display)) ? htmlspecialchars($query_to_display) : ''); ?>
</textarea><br />
        <input type="checkbox" name="show_query" value="1" id="checkbox_show_query" checked="checked" />&nbsp;
        <label for="checkbox_show_query"><?php echo $strShowThisQuery; ?></label><br />
    </div>
<?php
// loic1: displays import dump feature only if file upload available
if ($is_upload) {
    echo '    <i>' . $strOr . '</i> ' . $strLocationTextfile . '&nbsp;:<br />' . "\n";
    ?>
    <div style="margin-bottom: 5px">
        <input type="file" name="sql_file" class="textfield" /><br />
    <?php
    if ($cfg['AllowAnywhereRecoding'] && $allow_recoding) {
        $temp_charset = reset($cfg['AvailableCharsets']);
        echo $strCharsetOfFile . "\n"
             . '    <select name="charset_of_file" size="1">' . "\n"
             . '            <option value="' . $temp_charset . '"';
        if ($temp_charset == $charset) {
            echo ' selected="selected"';
        }
        echo '>' . $temp_charset . '</option>' . "\n";
        while ($temp_charset = next($cfg['AvailableCharsets'])) {
            echo '            <option value="' . $temp_charset . '"';
            if ($temp_charset == $charset) {
                echo ' selected="selected"';
            }
            echo '>' . $temp_charset . '</option>' . "\n";
        }
        echo '        </select>';
    } // end if
    echo "\n";
    ?>
    </div>
    <?php
} // end if
echo "\n";

// Bookmark Support
if ($cfg['Bookmark']['db'] && $cfg['Bookmark']['table']) {
    if (($bookmark_list = PMA_listBookmarks($db, $cfg['Bookmark'])) && count($bookmark_list) > 0) {
        echo "    <i>$strOr</i> $strBookmarkQuery&nbsp;:<br />\n";
        echo '    <div style="margin-bottom: 5px">' . "\n";
        echo '        <select name="id_bookmark">' . "\n";
        echo '            <option value=""></option>' . "\n";
        while (list($key, $value) = each($bookmark_list)) {
            echo '            <option value="' . $value . '">' . htmlentities($key) . '</option>' . "\n";
        }
        echo '        </select>' . "\n";
        echo '        <input type="radio" name="action_bookmark" value="0" id="radio_bookmark0" checked="checked" style="vertical-align: middle" /><label for="radio_bookmark0">' . $strSubmit . '</label>' . "\n";
        echo '        &nbsp;<input type="radio" name="action_bookmark" value="1" id="radio_bookmark1" style="vertical-align: middle" /><label for="radio_bookmark1">' . $strBookmarkView . '</label>' . "\n";
        echo '        &nbsp;<input type="radio" name="action_bookmark" value="2" id="radio_bookmark2" style="vertical-align: middle" /><label for="radio_bookmark2">' . $strDelete . '</label>' . "\n";
        echo '        <br />' . "\n";
        echo '    </div>' . "\n";
    }
}

// Encoding setting form appended by Y.Kawada
if (function_exists('PMA_set_enc_form')) {
    echo PMA_set_enc_form('    ');
}
?>
    <input type="submit" name="SQL" value="<?php echo $strGo; ?>" />
</form>


<?php
/**
 * Displays the footer
 */
echo "\n";
require('./footer.inc.php3');
?>
