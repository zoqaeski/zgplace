;(function($){
	$(function() {
		var base_path = "/img/railways/signalling/qevelia/";
		var vslow = '800,100';
		var slow = '400,150';
		var fast = '200,100';
		var vfast = '100,50';

		$('.b4a_gyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/b4a_off,cl/b4a_gy'});
		$('.b4a_yyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/b4a_off,cl/b4a_yy'});

		$('.b5ct_gyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/b5ct_off,cl/b5ct_gy'});
		$('.b5ct_yyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/b5ct_off,cl/b5ct_yy'});
		$('.b5ct_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/b5ct_off,cl/b5ct_g'});
		$('.b5ct_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/b5ct_off,cl/b5ct_y'});

		$('.m2a_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2a_off,cl/m2a_g'});
		$('.m2as_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2as_off,cl/m2as_g'});
		$('.m2as_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2as_r,cl/m2as_rl'});
		$('.m2as_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2as_off,cl/m2as_l'});

		$('.m2b_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2b_off,cl/m2b_y'});
		$('.m2bs_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2bs_off,cl/m2bs_y'});
		$('.m2bs_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2bs_r,cl/m2bs_rl'});
		$('.m2bs_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2bs_off,cl/m2bs_l'});

		$('.m3c_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3c_off,cl/m3c_g'});
		$('.m3c_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3c_off,cl/m3c_y'});

		$('.m3cs_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3cs_off,cl/m3cs_g'});
		$('.m3cs_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3cs_off,cl/m3cs_y'});
		$('.m3cs_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3cs_r,cl/m3cs_rl'});
		$('.m3cs_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3cs_off,cl/m3cs_l'});

		$('.m2rs_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2rs_r,cl/m2rs_rl'});
		$('.m2rs_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/m2rs_off,cl/m2rs_l'});

		$('.md2rs_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/md2rs_r,cl/md2rs_rl'});
		$('.md2rs_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/md2rs_off,cl/md2rs_l'});

		$('.m3rs_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3rs_r,cl/m3rs_rl'});
		$('.m3rs_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/m3rs_off,cl/m3rs_l'});

		$('.md3rs_rlf').addClass('animate').dataset({intervals: slow, frames: 'cl/md3rs_r,cl/md3rs_rl'});
		$('.md3rs_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/md3rs_off,cl/md3rs_l'});

		$('.m4ps_rl3f').addClass('animate').dataset({intervals: slow, frames: 'cl/m4ps_r,cl/m4ps_rl3'});

		$('.m5aps_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m5aps_off,cl/m5aps_g'});
		$('.m5aps_gyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/m5aps_off,cl/m5aps_gy'});
		$('.m5aps_rl3f').addClass('animate').dataset({intervals: slow, frames: 'cl/m5aps_r,cl/m5aps_rl3'});
		$('.m5aps_l3f').addClass('animate').dataset({intervals: slow, frames: 'cl/m5aps_off,cl/m5aps_l3'});

		$('.m5bps_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m5bps_off,cl/m5bps_g'});
		$('.m5bps_gyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/m5bps_off,cl/m5bps_gy'});
		$('.m5bps_rl3f').addClass('animate').dataset({intervals: slow, frames: 'cl/m5bps_r,cl/m5bps_rl3'});
		$('.m5bps_l3f').addClass('animate').dataset({intervals: slow, frames: 'cl/m5bps_off,cl/m5bps_l3'});

		$('.m6cps_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps_off,cl/m6cps_g'});
		$('.m6cps_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps_off,cl/m6cps_y'});
		$('.m6cps_gyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps_off,cl/m6cps_gy'});
		$('.m6cps_rl3f').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps_r,cl/m6cps_rl3'});
		$('.m6cps_l3f').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps_off,cl/m6cps_l3'});

		$('.m6cps1_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps1_off,cl/m6cps1_g'});
		$('.m6cps1_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps1_off,cl/m6cps1_y'});
		$('.m6cps1_gyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps1_off,cl/m6cps1_gy'});
		$('.m6cps1_rl3f').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps1_r,cl/m6cps1_rl3'});
		$('.m6cps1_l3f').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps1_off,cl/m6cps1_l3'});

		$('.m6cps3_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps3_off,cl/m6cps3_g'});
		$('.m6cps3_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps3_off,cl/m6cps3_y'});
		$('.m6cps3_gyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps3_off,cl/m6cps3_gy'});
		$('.m6cps3_rl3f').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps3_r,cl/m6cps3_rl3'});
		$('.m6cps3_l3f').addClass('animate').dataset({intervals: slow, frames: 'cl/m6cps3_off,cl/m6cps3_l3'});

		$('.d2a_gf').addClass('animate').dataset({intervals: slow, frames: 'cl/d2a_off,cl/d2a_g'});
		$('.d2a_yf').addClass('animate').dataset({intervals: slow, frames: 'cl/d2a_off,cl/d2a_y'});

		$('.d4a_ggf2').addClass('animate').dataset({intervals: slow, frames: 'cl/d4a_off,cl/d4a_gg'});
		$('.d4a_gyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/d4a_off,cl/d4a_gy'});
		$('.d4a_yyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/d4a_off,cl/d4a_yy'});

		$('.d4t_ggf2').addClass('animate').dataset({intervals: slow, frames: 'cl/d4t_off,cl/d4t_gg'});
		$('.d4t_gyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/d4t_off,cl/d4t_gy'});
		$('.d4t_yyf2').addClass('animate').dataset({intervals: slow, frames: 'cl/d4t_off,cl/d4t_yy'});

		$('.sd2m_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/sd2m_off,cl/sd2m_l'});
		$('.sd2_lf').addClass('animate').dataset({intervals: slow, frames: 'cl/sd2_off,cl/sd2_l'});


		$('.rp3t_45f').addClass('animate').dataset({intervals: slow, frames: 'cl/rp3t_off,cl/rp3t_45'});
		$('.rp3t_00f').addClass('animate').dataset({intervals: slow, frames: 'cl/rp3t_off,cl/rp3t_00'});

		$('.rg3t_45f').addClass('animate').dataset({intervals: slow, frames: 'cl/rg3t_off,cl/rg3t_45'});
		$('.rg3t_00f').addClass('animate').dataset({intervals: slow, frames: 'cl/rg3t_off,cl/rg3t_00'});

		$('.tv2d_lf').addClass('animate').dataset({intervals: vfast, frames: 'cl/tv2d_off,cl/tv2d_l'});
		$('.tv2d_yf').addClass('animate').dataset({intervals: vfast, frames: 'cl/tv2d_off,cl/tv2d_y'});

		$('.lr3r_lf').addClass('animate').dataset({intervals: fast, frames: 'cl/lr3r_off,cl/lr3r_l'});
		$('.lr3_lfy').addClass('animate').dataset({intervals: fast, frames: 'cl/lr3_y,cl/lr3_ly'});

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

