;(function($){
	$(function() {
		var base_path = "/img/railways/signalling/qevelia/";
		var vslow = '800,100';
		var slow = '500,200';
		var fast = '200,100';
		var vfast = '100,50';

		$('.b4a_yyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/b4a_off,cl/b4a_yy'});
		$('.m2b_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2b_off,cl/m2b_y'});
		$('.m2bs_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2bs_off,cl/m2bs_y'});
		$('.m3c_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3c_off,cl/m3c_y'});
		$('.m3cs_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3cs_off,cl/m3cs_y'});
		$('.m5ds_yyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/m5ds_off,cl/m5ds_yy'});
		$('.d2a_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/d2a_off,cl/d2a_y'});
		$('.d4a_yyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/d4a_off,cl/d4a_yy'});

		$('.m2a_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2a_off,cl/m2a_g'});
		$('.m2as_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2as_off,cl/m2as_g'});
		$('.m3c_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3c_off,cl/m3c_g'});
		$('.m3cs_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3cs_off,cl/m3cs_g'});
		$('.m5ds_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m5ds_off,cl/m5ds_g'});
		$('.d2a_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/d2a_off,cl/d2a_g'});

		$('.m2b_yff').addClass('animate').dataset({intervals: fast, frames: 'cl/m2b_off,cl/m2b_y'});
		$('.m2bs_yff').addClass('animate').dataset({intervals: fast, frames: 'cl/m2bs_off,cl/m2bs_y'});
		$('.m3c_yff').addClass('animate').dataset({intervals: fast, frames: 'cl/m3c_off,cl/m3c_y'});
		$('.m3cs_yff').addClass('animate').dataset({intervals: fast, frames: 'cl/m3cs_off,cl/m3cs_y'});
		$('.d2a_yff').addClass('animate').dataset({intervals: fast, frames: 'cl/d2a_off,cl/d2a_y'});

		$('.m2a_gff').addClass('animate').dataset({intervals: fast, frames: 'cl/m2a_off,cl/m2a_g'});
		$('.m2as_gff').addClass('animate').dataset({intervals: fast, frames: 'cl/m2as_off,cl/m2as_g'});
		$('.m3c_gff').addClass('animate').dataset({intervals: fast, frames: 'cl/m3c_off,cl/m3c_g'});
		$('.m3cs_gff').addClass('animate').dataset({intervals: fast, frames: 'cl/m3cs_off,cl/m3cs_g'});
		$('.m5ds_gff').addClass('animate').dataset({intervals: fast, frames: 'cl/m5ds_off,cl/m5ds_g'});
		$('.d2a_gff').addClass('animate').dataset({intervals: fast, frames: 'cl/d2a_off,cl/d2a_g'});

		$('.m2as_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2as_r,cl/m2as_rl'});
		$('.m2bs_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2bs_r,cl/m2bs_rl'});
		$('.m2rs_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2rs_r,cl/m2rs_rl'});
		$('.md2rs_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/md2rs_r,cl/md2rs_rl'});
		$('.m3cs_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3cs_r,cl/m3cs_rl'});
		$('.m3rs_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3rs_r,cl/m3rs_rl'});
		$('.md3rs_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/md3rs_r,cl/md3rs_rl'});
		$('.m5ds_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/m5ds_r,cl/m5ds_rl'});
		$('.rg3m_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/rg3m_r,cl/rg3m_rl'});

		$('.m4ps_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/m4ps_r,cl/m4ps_rl'});

		$('.m2as_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2as_off,cl/m2as_l'});
		$('.m2bs_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2bs_off,cl/m2bs_l'});
		$('.m2rs_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2rs_off,cl/m2rs_l'});
		$('.md2rs_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/md2rs_off,cl/md2rs_l'});
		$('.m3cs_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3cs_off,cl/m3cs_l'});
		$('.m3rs_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3rs_off,cl/m3rs_l'});
		$('.md3rs_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/md3rs_off,cl/md3rs_l'});
		$('.m5ds_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/m5ds_off,cl/m5ds_l'});
		$('.rg3m_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/rg3m_off,cl/rg3m_l'});
		$('.sd2m_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/sd2m_off,cl/sd2m_l'});
		$('.sd2_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/sd2_off,cl/sd2_l'});

		$('.b4a_gyf').addClass('animate').dataset({intervals: slow, frames: 'cl/b4a_g,cl/b4a_gy'});
		$('.m5ds_gyf').addClass('animate').dataset({intervals: slow, frames: 'cl/m5ds_g,cl/m5ds_gy'});
		$('.d4a_gyf').addClass('animate').dataset({intervals: slow, frames: 'cl/d4a_g,cl/d4a_gy'});
		$('.rg3t_gyf_r').addClass('animate').dataset({intervals: slow, frames: 'cl/rg3t_g+r,cl/rg3t_gy+r'});

		$('.b4a_gfy').addClass('animate').dataset({intervals: slow, frames: 'cl/b4a_y,cl/b4a_gy'});
		$('.m5ds_gfy').addClass('animate').dataset({intervals: slow, frames: 'cl/m5ds_yb,cl/m5ds_gy'});
		$('.d4a_gfy').addClass('animate').dataset({intervals: slow, frames: 'cl/d4a_y,cl/d4a_gy'});
		$('.rg3t_gfy_r').addClass('animate').dataset({intervals: slow, frames: 'cl/rg3t_y+r,cl/rg3t_gy+r'});

		$('.b4a_gyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/b4a_off,cl/b4a_gy'});
		$('.m5ds_gyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/m5ds_off,cl/m5ds_gy'});
		$('.d4a_gyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/d4a_off,cl/d4a_gy'});
		$('.rg3t_gyf2_r').addClass('animate').dataset({intervals: slow, frames: 'cl/rg3t_g+r,cl/rg3t_gy+r'});

		$('.b4a_gyff2').addClass('animate').dataset({intervals: fast, frames: 'cl/b4a_off,cl/b4a_gy'});
		$('.m5ds_gyff2').addClass('animate').dataset({intervals: fast, frames: 'cl/m5ds_off,cl/m5ds_gy'});
		$('.d4a_gyff2').addClass('animate').dataset({intervals: fast, frames: 'cl/d4a_off,cl/d4a_gy'});
		$('.rg3t_gyff2_r').addClass('animate').dataset({intervals: fast, frames: 'cl/rg3t_g+r,cl/rg3t_gy+r'});

		$('.d4a_ggf2').addClass('animate').dataset({intervals: slow, frames: 'cl/d4a_off,cl/d4a_gg'});
		$('.d4a_ggff2').addClass('animate').dataset({intervals: fast, frames: 'cl/d4a_off,cl/d4a_gg'});

		$('.rp3t_45f').addClass('animate').dataset({intervals: slow, frames: 'cl/rp3t_off,cl/rp3t_45'});
		$('.rp3t_45ff').addClass('animate').dataset({intervals: fast, frames: 'cl/rp3t_off,cl/rp3t_45'});
		$('.rp3t_00f').addClass('animate').dataset({intervals: slow, frames: 'cl/rp3t_off,cl/rp3t_00'});
		$('.rp3t_00ff').addClass('animate').dataset({intervals: fast, frames: 'cl/rp3t_off,cl/rp3t_00'});

		$('.tv2d_lf').addClass('animate').dataset({intervals: vfast, frames: 'cl/tv2d_off,cl/tv2d_l'});
		$('.tv2d_yf').addClass('animate').dataset({intervals: vfast, frames: 'cl/tv2d_off,cl/tv2d_y'});

		$('.lr3_yf').addClass('animate').dataset({intervals: fast, frames: 'cl/lr3_off,cl/lr3_y'});
		$('.lr4_yf').addClass('animate').dataset({intervals: fast, frames: 'cl/lr4_off,cl/lr4_y'});
		$('.lr4_lf').addClass('animate').dataset({intervals: fast, frames: 'cl/lr4_off,cl/lr4_l'});
		$('.lx3_rf').addClass('animate').dataset({intervals: '200', frames: 'roads/lx3_r_left,roads/lx3_r_right'});
		$('.lx3_lf').addClass('animate').dataset({intervals: slow, frames: 'roads/lx3_off,roads/lx3_l'});

		$('.af_lf').addClass('animate').dataset({intervals: fast, frames: 'cl/af_off,cl/af_l'});

		$('.ab4_gf').addClass('animate').dataset({intervals: fast, frames: 'cl/ab4_off,cl/ab4_g'});
		$('.ab4_l1gf').addClass('animate').dataset({intervals: fast, frames: 'cl/ab4_l1,cl/ab4_l1g'});

		// Animation defined here
		$('.animate').each(function(index, value) {
			$(this).animateImg({base_path: base_path});
		});
	});
})(jQuery);

