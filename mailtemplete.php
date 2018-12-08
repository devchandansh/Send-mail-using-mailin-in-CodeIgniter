<?php

/*
*|======================================================================|
*|	@Author : Chandan Sharma 											                      |
*|	@Email: <devchandansh@gmail.com>									                  |
*|	@Website: <www.chandansharma.co.in>									                |
*|======================================================================|
*/	

/*
  DB Table Stucture:
  type(varchar),subject(varchar),template(text),status(Y,N)

  from = devchandansh@gmail.com, to = "devchandansh@gmail.com",
  
  templete_type = <template_type>
  param = array( "{name}" => "Chandan Sharma" )

  Function call:
  send_mail($from,$to,$templete_type,$param)
 */

class Mailtemplete {

    public function __construct() {
        $CI = &get_instance();
        $config = Array(
            'mailtype' => 'html',
            'charset' => 'utf-8'
        );
        $CI->load->library('email', $config);
    }

    public function send_mail($from = "", $to = '', $templete_type = '', $param = array()) {
        $CI = &get_instance();
        $templete = $this->get_templete($templete_type, $param);

        if (!$templete) {
            return false;
        }
        
        $subject = $templete['subject'];
        $body    = $templete['template'];

         //# RAW CODE:
        $CI->load->library('Mailin');

        //For from Email
        // $from       = SITE_EMAIL_FROM;             
        if($from == ""){
            $from = SITE_EMAIL_FROM;
        }
        
        //For ReplyTO Email
        $replyTo    = SITE_EMAIL_REPLYTO;

         //For pram name for  Email
        $name = '';
        if(!empty($param) && !empty($param['{name}']) ){
            $name = $param['name'];
        }
            
        
        $CI->mailin->
        setTo($to, $name)->
        setFrom(SITE_EMAIL_FROM, SITE_TITLE)->
        setReplyTo(SITE_EMAIL_REPLYTO, SITE_TITLE)->
        setSubject($subject)->
        setHtml($body);
        $send = $CI->mailin->send();
            
        return $send;
    }
    
    //=====================================================================================
    //  Function TO Get the Template Dynamic Contents From the Database
    //=====================================================================================
    
    public function get_templete($template_type = '', $param = array()) {
        //-----------------------------------------------
        //  Getting Template Contents from the Database
        //-----------------------------------------------
        $CI = &get_instance();
        $mail_body = $CI->db->select("subject,template")->get_where('email_template', array("type" => $template_type))->row_array();
        
        
        /*
        //------------------------------------------
        // Format of Returned Data
        //------------------------------------------
        $mail_body = array();
        $mail_body['template'] = "YOUR EMAIL TEMPLATE CONTENT";
        $mail_body['subject'] = "YOUR EMAIL SUBJECT";
        
        */
        
        
        if (is_array($mail_body) && count($mail_body) > 0) {
        
            $template = html_entity_decode($mail_body['template']);            
            $subject = $mail_body['subject'];
            
            //-----------------------------------------------
            //  Replacing the VARIABLE with proper VALUE
            //-----------------------------------------------
            foreach ($param as $key => $value) {
                $template = str_replace($key, $value, $template);
                $subject = str_replace($key, $value, $subject);
            }
            
            //-----------------------------------------------
            // For Template Content VARIABLE Replacement
            //-----------------------------------------------
            $template   = str_replace("{{SITE_LOGO}}", SITE_LOGO , $template);
            $template   = str_replace("{{SITE_TITLE}}", SITE_TITLE, $template);
            $template   = str_replace("{{SITE_LINK}}", SITE_LINK, $template);
            
            //-----------------------------------------------
            //  For Email Subject
            //-----------------------------------------------
            $subject    = str_replace("{{SITE_TITLE}}", SITE_TITLE, $subject);
            
            
            // Returned Parameters
            $mail = array(
                'subject' => $subject,
                'template' => $template
            );
           
            return $mail;
        } else {
            return FALSE;
        }
    }

}

?>
