<?php
/**
 * 专题页
 *
 * 
 */

defined('In718Shop') or exit('Access Invalid!');

class ztyControl extends BaseHomeControl {
    //const PAGESIZE = 16;
    public function app_indexOp() {
        
            Tpl::showpage('zty.app');
        }
    public function milk_indexOp() {
        
            Tpl::showpage('zty.milk');
        }
    public function europe_indexOp() {
        
            Tpl::showpage('zty.europe');
        }

    public function two_years_indexOp() {
        
            Tpl::showpage('zty.2years');
        }
    public function two_eleven_indexOp() {
        
            Tpl::showpage('zty.1111');
        }
    public function two_twelve_indexOp() {
        
            Tpl::showpage('zty.1212');
        }
        public function happy_new_year_indexOp(){
            Tpl::showpage('zty.happy_new_year');
        }
		 public function vdayOp() {

        Tpl::showpage('zty.VDay');
    }
	 public function womenDayOp() {

        Tpl::showpage('zty.38');
    }
	 public function dijiaOp() {

        Tpl::showpage('zty.dijia');
    }
	
	  public function zty_718Op() {

        Tpl::showpage('zty_718');
    }
	public function blgOp() {

        Tpl::showpage('zty.blg');
    }
	public function zty_77Op() {

        Tpl::showpage('zty.77day');
    }
	public function D_daysOp() {

        Tpl::showpage('zty.D_days');
    }
    public function zty_double11Op() {

        Tpl::showpage('zty.double11');
    }
	public function zty_double11sOp() {

        Tpl::showpage('zty.double11s');
    }
	public function zty_315Op() {

        Tpl::showpage('zty_315');
    }
    public function zty_51Op() {

        Tpl::showpage('zty.51day');
    }
    public function zty_MdayOp() {

        Tpl::showpage('zty_MothersDay');
    }
    public function zty_dwOp() {

        Tpl::showpage('zty.dw');
    }
	public function zty_worldcupOp(){
		Tpl::showpage('zty.WorldCup');
	}
	public function zty_5yearsOp(){
		Tpl::showpage('zty_5years');
	}
	public function zty_718fOp(){
		Tpl::showpage('zty_718f');
	}
	public function zty_magpieOp(){
		Tpl::showpage('zty_magpie');
	}
    public function zty_supervipOp() {

        Tpl::showpage('zty_supervip');
    }
	public function zty_zhongqiuOp() {

        Tpl::showpage('zty.zhongqiu');
    }
	
}