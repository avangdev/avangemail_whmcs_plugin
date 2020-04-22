<?php
/* @propery $D D */

use WHMCS\Database\Capsule;

defined('mname') OR die('404 Not Found');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12">

            <ul class="nav nav-tabs client-tabs" role="tablist">  
<?php foreach ($D->tabs as $k => $v) { ?>
                    <li class="<?= $k == $D->tab ? 'active' : 'tab' ?>" >
                        <a href="<?= $D->mlink . '&tab=' . $k ?>" > <i class="<?= $v[0] ?>" ></i> <?= $v[1] ?></a>  
                    </li>
<?php } ?>    
            </ul>
            <!-------------------------------------------------->   

            <div class="tab-content client-tabs" >  
                <div class="tab-pane active">     

                    <?php
                    if (AVANG_MAIL_HOOK\get('ok')) {
                        echo '<div class="alert alert-success">' . AVANG_MAIL_HOOK\get('ok') . '</div>';
                    }
                    if (AVANG_MAIL_HOOK\get('err')) {
                        echo '<div class="alert alert-danger">' . AVANG_MAIL_HOOK\get('err') . '</div>';
                    }
                    ?>    


                    <?php
                    if ($D->tab == 'settings'):


                        if (AVANG_MAIL_HOOK\post('sbm')) {

                            foreach ($_POST as $k => $v) {
                                if ($k !== 'sbm') {
                                    Capsule::table('mod_avang_mail_settings')->where('word', '=', $k)->update(array('value' => $v));
                                }
                            }
                            AVANG_MAIL_HOOK\obclean();
                            header('location:' . $D->current . '&ok=infromation was submitted');
                            exit;
                        }
                        ?>
                        <?php if ($D->settings) : ?>
                            <form action="" method="post">   
                            <?= $D->table_form ?>
                            <?php foreach ($D->settings as $word => $items) : ?>   
                                    <tr>   
                                    <?= $D->td_label . $items->label . $D->close_td ?>
                                    <?= $D->td_area ?> 
                                    <?php if ($items->type == 'text') { ?>

                                        <input style="<?= $items->ltr ? 'direction:ltr;text-align:left;' : '' ?>" type="text" class="form-control input-400" name="<?= $word ?>" value="<?= $items->value ?>" /> 

                                        <?php } elseif ($items->type == 'textarea') { ?>    
                                        <textarea  style="<?= $items->ltr ? 'direction:ltr;text-align:left;' : '' ?>" class="form-control input-400" name="<?= $word ?>" ><?= $items->value ?></textarea>  
            <?php } elseif ($items->type == 'checkbox') { ?>    
                                        <input type="checkbox" style="display:none;opacity:0"   class="" name="<?= $word ?>" value="0" checked />

                                        <input type="checkbox"   class="" name="<?= $word ?>" value="1" <?= $items->value == "1" ? 'checked' : '' ?> />

            <?php } elseif ($items->type == 'password') { ?>
                                        <input style="<?= $items->ltr ? 'direction:ltr;text-align:left;' : '' ?>" type="password" class="form-control input-400" name="<?= $word ?>" value="<?= $items->value ?>" />  
            <?php } ?>
                                    <strong><?= $items->help ?></strong>
                                    <?= $D->close_td ?>     
                                    </tr>   
                                <?php endforeach; ?>  
                                <tr> 
                                <?= $D->td_label . $D->close_td ?>
                                <?= $D->td_area ?>
                                <button type="submit" class="btn btn-block btn-success" value="1" name="sbm">submit information</button>
                                    <?= $D->close_td ?>      
                                </tr>    
                                    <?= $D->close_table ?>    
                            </form>    
                                <?php
                            endif;
                            ?>

                        <!----------------------------------------------------->  
                    <?php
                    elseif ($D->tab == 'send') :

                        if (AVANG_MAIL_HOOK\post('send')) {
                            $id = AVANG_MAIL_HOOK\post('ID');
                            $id = trim($id) ? trim($id) : $D->settings['lists']->value;
                            if (!$id) {
                                AVANG_MAIL_HOOK\obclean();
                                header('location:' . $D->current . '&err=The ID list is not defined');
                                exit;
                            }

                            $clients = Capsule::table('tblclients')->select(array('firstname', 'lastname', 'email'))->get();
                            $o = array();
                            if (!empty($clients)) {
                                foreach ($clients as $client)
                                    $o[] = array(
                                        'EMAIL' => $client->email,
                                        'FNAME' => $client->firstname,
                                        'LNAME' => $client->lastname
                                    );
                            }
                            AVANG_MAIL_HOOK\obclean();
                            if (!empty($o)) {
                                $AL = & AVANG_MAIL_HOOK\list_obj();
                                $response = $AL->createBulk($id, $o);
                                if ($response->httpMessage == 'Created') {
                                    header('location:' . $D->current . '&ok=The process was successful');
                                    exit;
                                } else {

                                    header('location:' . $D->current . '&err=An error occurred');
                                    exit;
                                }
                            }
                            header('location:' . $D->current . '&err= The list of customers is empty');
                            exit;
                        }
                        ?>  
                        <div class="alert alert-info">
                            By clicking the button below, all users will be imported to the desired email list<br/>
                            If you do not enter the list ID it select the default ID
                            <a href="<?= $D->mlink ?>&tab=settings" > settings </a> from
                        </div>    
                        <br/>
                        <form action="" method="post" >
                            <div class="row">
                                <div class="col-md-6 col-xs-12 col-md-pull-3">

                                    <div class="form-group col-md-6 col-xs-12">
                                        <input type="text" class="form-control" name="ID" />  
                                    </div>

                                    <div class="form-group col-md-6 col-xs-12">
                                        <button onclick="if (!confirm('Are you sure?'){event.preventDefault(); return false; })" type="submit" name="send" value="1" class="btn btn-block btn-info">Submit</button>    
                                    </div>   


                                </div>        
                            </div>
                        </form>

    <?php
endif;
?>
                </div>   
            </div>    
        </div>   
    </div>   
</div>   