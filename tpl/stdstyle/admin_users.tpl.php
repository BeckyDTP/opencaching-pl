<?php

?>
<div class="content2-pagetitle"><img src="tpl/stdstyle/images/blue/aprove-cache.png" class="icon32" alt="{{profile_data}}" title="{{profile_data}}" align="middle" />&nbsp;{{management_users}}: {username}</div>

<div class="content-title-noshade box-blue">
    <p><img src="tpl/stdstyle/images/blue/arrow2.png" alt="" align="middle" />&nbsp;&nbsp;<span class="content-title-noshade txt-blue08" >{{user_ident}}:</span><strong> &nbsp;&nbsp;{username}</strong>&nbsp;&nbsp;&nbsp;<img src="tpl/stdstyle/images/blue/arrow.png" alt="" /> (<a href="viewprofile.php?userid={userid}">{{user_profile}}</a>)</p>
    <p><img src="tpl/stdstyle/images/blue/arrow2.png" alt="" align="middle" />&nbsp;&nbsp;<span class="content-title-noshade txt-blue08" >{{registered_since_label}}:</span><strong>&nbsp;&nbsp; {registered}</strong></p>
    <p><img src="tpl/stdstyle/images/blue/arrow2.png" alt="" align="middle" />&nbsp;&nbsp;<span class="content-title-noshade txt-blue08" >{{email_address}}:</span> &nbsp;&nbsp;{email}&nbsp;&nbsp;<img src="tpl/stdstyle/images/blue/email.png" width="22" height="22" alt="Email" title="Email" align="middle"/>&nbsp;<a href="mailto.php?userid={userid}">{{email_user}}</a></p>
    <p><img src="tpl/stdstyle/images/blue/arrow2.png" alt="" align="middle" />&nbsp;&nbsp;<span class="content-title-noshade txt-blue08" >{{activation_code}}:</span> <strong>&nbsp;&nbsp;{activation_codes}</strong></p>
    <p><img src="tpl/stdstyle/images/blue/arrow2.png" alt="" align="middle" />&nbsp;&nbsp;<span class="content-title-noshade txt-blue08" >{{country_label}}:</span><strong> &nbsp;&nbsp;{country}</strong></p>
    <p><img src="tpl/stdstyle/images/blue/arrow2.png" alt="" align="middle" />&nbsp;&nbsp;<span class="content-title-noshade txt-blue08" >{{descriptions}}:</span> <strong>&nbsp;&nbsp;{description}</strong></p>
    <p><img src="tpl/stdstyle/images/blue/arrow2.png" alt="" align="middle" />&nbsp;&nbsp;<span class="content-title-noshade txt-blue08" >{{lastlogins}}:</span> <strong>&nbsp;&nbsp;{lastlogin}</strong></p>
    <br />
    <hr></hr>
    <p><img src="tpl/stdstyle/images/blue/arrow2.png" alt="" align="middle" />&nbsp;&nbsp;<span class="content-title-noshade txt-blue08" >{is_active_flags}</span></p>
    <p><img src="tpl/stdstyle/images/blue/arrow2.png" alt="" align="middle" />&nbsp;&nbsp;<span class="content-title-noshade txt-blue08" >{stat_ban}</span></p>
    {hide_flag}
    {remove_all_logs}
    {ignoreFoundLimit}
    <hr></hr>
    <br />
    <p><img src="tpl/stdstyle/images/blue/arrow2.png" alt="" align="middle" />&nbsp;&nbsp;<span class="content-title-noshade txt-blue08" >{form_title}:</span></p>
    <form action="admin_users.php?userid={userid}" method="post" name="user_note">
        <table id="cache_note1" class="table">
            <tr valign="top">
                <td></td>
                <td>
                    <textarea name="note_content" rows="4" cols="85" style="font-size:13px;"></textarea>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <button type="submit" name="save" value="save" style="width:100px">{submit_button}</button>
                </td>
            </tr>
        </table>
    </form>
</div>
