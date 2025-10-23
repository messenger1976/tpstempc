<style type="text/css">
    #receipt{
        width: 600px;
        border: 1px solid #ccc;
        margin: auto;
        padding: 10px;
    }
    table#receipt_header{
        width: 580px;
        margin: auto;
    }
    table#receipt_header tr td{
        vertical-align: middle;
    }
    table#receipt_header tr td#logo_receipt{
        width: 150px;
        border: 0px;
    }
    table#receipt_header tr td#logo_receipt img{
        width: 120px;
        height: 100px;
    }

    table#receipt_header tr td#receipt_title{
        font-weight: bold;
        font-size: 15px;
        text-align: center;
        vertical-align: middle;
        border: 0px;
    }

    table#receipt_after_title{
        width: 580px;
        table-layout: fixed;    
        margin: auto;
        padding-top: 10px;
    }
    
    table#receipt_after_title tr td{
        padding-bottom: 15px;
    }
</style>

<div id="receipt">
    <table id="receipt_header">
        <tr>
            <td id="logo_receipt">
                <img src="<?php echo base_url() ?>logo/<?php echo company_info()->logo; ?>"/>
            </td>
            <td id="receipt_title">
                <?php echo company_info()->name; ?><br/>
                <?php echo 'P.O.Box' . company_info()->box .' , '.  lang('clientaccount_label_phone').':' . company_info()->mobile; ?><br/>
                CASH CONTRIBUTION  DEPOSITE/REFUND FORM<br/>
                FOMU YA KUWEKA/KURUDISHIWA MICHANGO 
              </td>
        </tr>
    </table>
    <div style="height: 10px; border-top: 1px solid #ccc;"></div>
    <table id="receipt_after_title">
       <?php //$deposited_to = client_user_info($trans->FROM_TO_PIN, TRUE); ?>
        <tr>
            <td>Date/Tarehe <br/> <b> <?php $ex = explode(' ', $trans->createdon); echo format_date($ex[0], FALSE) ; ?></b></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Member Number <br/>Namba ya Utambulisho<br/><b> <?php echo 'SYSTEM No : ' .$trans->PID.' <br/>Other No : '.$trans->member_id ; ?></b></td>
            <td>Number Holder's Name<br/>Jina la Mwenye Namba<br/><b>   <?php
            $account_name = $this->member_model->member_basic_info(null,$trans->PID,$trans->member_id)->row();
          echo $account_name->firstname.' '.$account_name->middlename.' '.$account_name->lastname;  ?></b></td>
        </tr>
        <tr>
            <td>Deposit/Refund<br/>Kuweka/Kurudushiwa<br/><b> <?php echo ($trans->trans_type == 'CR' ? lang('CONTRIBUTE'): lang('REFUND')) ; ?></b></td>
            <td>Amount<br/>Kiasi cha Fedha<br/><b>   <?php echo number_format($trans->amount,2).' &nbsp;  '.$trans->trans_type;  ?></b></td>
        </tr>
        <tr>
            <td>Transaction Number <br/>Namba ya Muamala<br/><b> <?php echo $trans->receipt; ?></b></td>
            <td>Teller Name<br/>Jina la Mhasibu Fedha<br/><b>   <?php $use = current_user($trans->createdby); echo $use->first_name.' '.$use->last_name;  ?></b></td>
        </tr>
        
        <tr>
            <td colspan="2" style="height: 30px;"></td>
        </tr>
       
        <tr>
            <td>Customer/Mteja <br/><br/>.....................................</td>
            <td>Teller/Mhasibu Fedha<br/><br/>.......................................................</td>
        </tr>
       

    </table>
    <br/>
    
</div>

<div style="margin: auto; text-align: center; margin-top: 20px;">
            <?php echo anchor(current_lang().'/contribution/print_receipt/'.$trans->receipt,  lang('print_receipt'),'class="btn btn-primary"'); ?>
    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
    
            <?php 
            if($this->session->flashdata('next_customer') && $this->session->flashdata('next_customer_label')){
            echo anchor($this->session->flashdata('next_customer'),  $this->session->flashdata('next_customer_label'),'class="btn btn-primary"'); 
            
            }
            ?>
    
    
   
</div>