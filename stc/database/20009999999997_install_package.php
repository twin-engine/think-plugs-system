<?php

use think\admin\extend\CodeExtend;
use think\admin\extend\PhinxExtend;
use think\admin\model\SystemConfig;
use think\admin\model\SysMenu;
use think\admin\model\SystemUser;
use think\migration\Migrator;

@set_time_limit(0);
@ini_set('memory_limit', -1);

/**
 * 数据安装包
 * @class InstallPackage
 */
class InstallPackage extends Migrator
{
    /**
     * 数据库初始化
     * @return void
     * @throws \think\db\exception\DbException
     */
    public function change()
    {
        $this->inserData();
        $this->insertConf();
        $this->insertUser();
        $this->insertMenu();
    }

    /**
     * 安装扩展数据
     * @return void
     * @throws \think\db\exception\DbException
     */
    private function inserData()
    {
        // 待解析处理数据
        $json = <<<JSON
[]
JSON;
        // 解析并写入扩展数据
        if (is_array($tables = json_decode($json, true)) && count($tables) > 0) {
            foreach ($tables as $table => $zipstr) if (($model = m($table))->count() < 1) {
                $model->strict(false)->insertAll(json_decode(CodeExtend::dezip($zipstr), true));
            }
        }
    }

    /**
     * 初始化系统菜单
     * @return void
     * @throws \think\db\exception\DbException
     */
    private function insertMenu()
    {
        // 检查数据
        if (SysMenu::mk()->count()) return;

        // 解析并初始化菜单数据
        $menu = "eJzNnVtzHLeVx7_KFt8TcYbXmTzuvuRhXamtyr6yWmSTntLcMjO0zE2pirItUbRFUYoulGTJElmypWwi0us4Nk3S4pdhz-VbLG7dDaBxOd0NsJKqVBgKHMyvgcb545yDg6Bema7_uVGf_l1Qr9X_3K_P1qfaQSuc-l2_XqnWp0ZvXw3v_Dw63B_dv41_h_65sdxpk3-eqU-t98PeWq-z3v1NsLLC_n25s0L_fKE-1d_oLw3CdtAeLLWC9lrYY20GG13UBnfbr8_Xp9AnDOi_VSr1qSvpX-Ff1fBHtrqddkj__2J96g_BWvjfjfA6-Yvp-lQ37LUa_X6DfjH0i_ijgm632VgOBuxf5tBvVlqNNvsW_fWr_SkEPlvwCbCOeOZpnlmCrciw6AFd0XNWauSzBmGLNbrSaK-EnzpiRqPHoBdl6EUROrqzN95_J6B_JFHPCyOdfE2OvSqyf5Th_UiJlT6DOv3wevoMTKg38NO2o12cv4zePzWizQlo_eCT0BHZokwWf7YNrAoAG3794_DJ90Yw8e1c764EA0do1WkZLf10G9wMaEK-njx7Y38XRcKVsBnaCJOPkd_F-PUCsPbCVgc2kLNm1i9_Gm7eHD76Jdp9amcV38D-wDqY-VGz72LSjY10TkWarK3DV9-Ojw7yMv5pPext-GdMurExzhsZFYuoklFcbpqNvmxEyiNmFp64FxvhgoKwlgzi6wd56Qa90P0kzdDFvdjoFhV0VfTzZPP5-HyLQo7v_RztPhnvvoh2HttpawLtWog0UNhed74CZaYs6gl3dHUj1RY2-BoYPjp5NHoEmMiLGXjUv3P2zGijjlA_edArSg2EXtzh9sPh3z-MD9-Mj27mHexBZ6mFvr7z9alayUzvzn_GHVlJdZKIjizlnWw9mBy8tPJWp7OT2wtwVTHCcGKlVkoX5cP94etjO2vy0GPc8NMAdeFeTGRGl-vIzHqDKV79Dmb843fR7s_giYz_i3d3eN-GdlsSamYrU2PbNvwnKuZUJ-Fmf0StnO5mFnSbmZnpRE89epc8hejO7cn-P6E2eVF6IoD9TSkhif4H9-Nqq0ORR2dPxh8AJnpBgg1XGs4lCDfPY1b4DsG8_aGwaO8THXxjh52XYFMnhg9FGbNCN3rmvVA8k2F7Ifmt9rQbyg5sue0Q1iDCLM61KZJfXC_bIsWLW2pfJCNHR7dHr29OtnZGvx7aTVVVQu6FfWSau9edz-vqjGKoUV-sq0K7JfktBu4L5WH2sjNUDDNwa3iDLVoq0zzev3tx_ByZo9H_nQqgsreGMSKTsN4f9DaUNkhplOO_UBrl5HVNml2OVcZznEfX2mPpMeC_yz4Glw5HbgWLOylvh3lUu8uRvVkypDvXIzeVE8ayzkce0e58rM4pEZ06IatZyPJuSHHSZkwvDDM1iX4wy3sgeczx0SnaHBbAXEFb8UbTH2b6-YXcj_KkzRob2OIDsDZlFp9S_kd5nR0-O5xsPgess-olyO6OLLMEwdyQN9jbadjovv3LePsHozUV_ZC9ThO8v43bmve3_4VaXZ4l5bHBlrSSeQRO43YLyfjij3a2lSWMABM6naVzGLqbF-HcBO4oG8B2VrNsLg0n50EndK5id_H8tBpNBZ9LiynzuYrXUT6AtVTwuTSVMl95MynMT7uZVKwsDm2kvLKUto8CHdQ-KtYXd8ZRXl-ghtHkAx4-2bo4_Umdx4O-wGqnuRL2FEk8zG-02miGfYkuk7sT20jcWGUjSyTuJE9EsosVY-bOP05Hp68ouWk00Y_xVzZJAPSsr-jQ0llJmhSz_VrIRWOqDkcJ9WhPU2I_nmzuDcWdGG0_h2wy_hyiwvirEGuM0G71CwDOi4AG-y_yaQUAx6cQAMohrDBAiOkvFUYniEYRIEJqVYAwT2Fe6xjSj7dahjQqARFSKwU4SIUUMM3UXhi4Dz7My4SBzjsr8mmlAD-IR2fRrW9NC2syfg30ZXuuto5zUjSWrTJJF1Y4re9Zgts6AcGFn3qHS7uwwqmSdDDcxfGXF2ev2RoDdKzPpsbfU7qVZCpMYk7EVGXryJijt6fRlwBrOMNh2vcZ5d9C8CqjysrBkAwvVyyMH0s_6YHSWJqCYJLZ12bg5BI1_DB6SZyThtEgyyU-na6J-W6PTr_ImSBIku496bfULJJe8gg4pcKZE0lBmYKzPKaXRMEFidKQJyhBKhUOmq75BlIg9DJfZUL4hNXJG34YgUJc5PQix2VOsB6v6GSOMF2Bgnye5_SkymsSaQ5ZXtGpHnERgulygRXinHPAmkOdV5QiKDEok4Mvxm_vgCmJQen2wk-Y38ITJbEpXDcWyhvGvILo8Plw76eLUySA9iVQ8n2Xr4UDGZf57ZAGW0eKXSLNHtSaZt4e0l4F7f6cltHbQ2GB57RqHKtEmvH7oP9zRQuZGkvaxmnUx-j34Xlzigc2xJ7FA-mlfOQnun-EVDwVuzRR2coZu2eDbiNm7S9dTx6vj-Rk1NVvGXCf9VQoHITf3Vv_mOy9V767-uSnFLU_6LjPs68oQOOOioaFxAmcy_BQUk9eoZo0g0sGiKrxFIaOaC07ooHNv-Bo7iY9FT3QRRmByYqxNKSUvXCl43wsFzJjSTspepiL2tXh8S3QYWBhuV1uhoHzFSiz3Ca9FDnMRZZbTjlA_UQCpx9HkcwJzr005YtEd0-iV6-UkgF927Vm52rQlFjj5bYXBhKjNhwWt_Wvj6r2aBhFFmBlv-ZsighLM8WBMR0kFxjDTZzKo6o9LEZxc3qQCLnnHTnuo6Q2mhMhQW4VntCLV2VeJIQdvjQeXtr-6_CfN6Nbf492xZNa6rQK7DnvdpAdW0MrLvrKQVteizLTFz_IK-xv_j39k0yGV9JB3PgK14H3HC_uMYBzvBZ1j8RpxnQ6qcWH6CDpi4MGJH0taHEd5k7P62hLZ4FxsIAssJoW1mkW9aIO10FamDClrWlhemCn-dRaYAd5Yjzw_bvD92LwVLmTNczpjfay6-XbMLtZb4VzyPjZbc8h069cLtOttStX-aQyHheaVKYfa4eJ19oRzpNlNmvUmKOvHwzvvc0KaqPbsdVpNwadnk1U416uSO39C-sZk7CObt-KDn9RQmsdj2tIF8mbQaXjMWlocDySNk6V9axJWfO8uaOW6JteQtQS9eLA8chx5twJU07fO2HKCV6rdMKD5xwffTf8_Jadc57nhGQvlwwAYVB4IrNOcYgzN2eoC5NeQqgLk0KlhmnfFL14Nzo5p0UP7DJjRliElzrtZqMdJsUcjLsnXLCPtv-jrvZDclSYa3h5ngD-SUDXKzbq7Dn4WbDSUafdlFyx8JifnUw2N6FjPi0wXmssX1MNuMsFi3HirmCTWxnexOkkj78f7hyikQRa3Qo7c4G2LMHVoG8N-WHXHd_WcHA6buZ0Ps9rXQEVDp4u1sg0jV58ZX8ENfER-Fq109HGPeVYtHU-rgT34uxpdBcQ_KtIoJ3uoNFq_I_zZZs7w0hQ-X4K2WIB9vwwerOTf1R7YTdo-HyHCWraSxFTLExiGksZHdwbbW_lH9vVXrDWCjOVVp2PLd9Poe0_P7Y561oksL5TcAkpWE4qd_o8Zs7ySgmm70MpBBNoa-l2d06nsc42o90HyvgR-nGt05RPU1VSJ_Wg02laT1QlSTZJa_973UV7EGlyKxPflYNIieRbQv-82lizWVxcCjltmdnsJlqJNboca5vGkSgxoBJyTeL2VAyZfrgDDztHByiGvCjTeamHzOBKO9Q5NoBDfVpm81QSmdE58J8LMxO0m81Ceq-KzHAdeM_54QRa0Mzb6LtoMKMtZUSJ_5iDzXfOI4PsuxZ0_LbCap4ZQ72_Phj97dn48IPNuDAV2A8Hg0bbaloWsQ1Nm5oqd9BWTo3LnGkrxxNP9n-BptTPC_xekupnuEp-tBuyHtNfpYa6iN1RgEPzOqsiOK46GydEO17AFjL4qDP0Gzi6tlIWrSF9fn-8f3f4bj8627Vzx2kb3cZSb72Ntpk4o3UlvLouT_7y4FwCR7fxW9ZbneutsPP1ztZw5yAfdyXL7Yd6VkkNZtael6GbW51oNs1xnlkpoctDzymhoTPcdPxg-HDn4tcXw71vo_M900rOvBedbrNjXcZxNCxpaIiGkTZOl3DjTSk8as4oEfmm3qNEpBe4ItGeVeQ4obGTGZ4TKU2PKoRSwlWmanXGq4CImadKtDCovkOcFBfsxNCnwE4-O1TXhVG_qWEr6zNWvqlJQ8ObStpcTpk03CtFLTSo5Jv6c0wRlDrppHwQiGHuPphsAq4tmOUB-2HbSwkKni_uo6h84ics4JajeZ7PdZk0nqv8fp4ns-fDiWSuC6TxZOW37jyZvUCaSOa6NBpPVr40mjAbrWlt4nLiuCgaD1bu0qKES5u5Zl9KfBVg4CnhBUN1-Wq0WojC42CKtSXb7kGnB7gZA3_rxLPA_Y0hv1zR2mdSF9Y90WfvovdPo7PHw4f3cG3jb76C7slnlU9liT4PbzsWflfOPaUi7ghMj9DRAyD00e5nw8ffl6T3Mf0VLpmUvpxfQv0MoDtWzTO4LLdUgWegPZJ68mD0t6Nch4aqsjOO0ffCtUYSCvThqODmQNqXfSnUxjK3dxA6wPkq8aJPlmsFKA-Ixe0Mob142Yub-j5TwyODz9RUs_ieCifjj3aQxcpBAs7QVBR4XionE7qyIT4eDhDim1HAeSqdTPDK7wnEKWo9I6MC9FQ7mQCW3xrwgIDaySpAT8WTCWD5HYIwRe0HX1TLi5_qyYSv9EEXAQ960EW1yHgpn0wY4duEeZ1tfL8X3ToGlpaJ05kayzKP0i7G7QwHp3ETt3d_G_NLKW20eZavtkwt5Sa5XJ6uyUvLViQ9lTOSCmRoHHZRRvYhebnaKylxGcOpAAbWOItTxlNgP3XOuLvFUuRyxlQ5sfPkzHDQnnJmVNAlb88ToAukkfBT28vdeaq3udzteYrJnStZiEP2c4-cCrm0vw4xDl_dvzj-34TdPq-rmXnd63T_o3Pd_aZ1ToGMO1thnRWqVTPHDXTeVOME2YefRrlgQ-9Y191CziQI8czlC2Hx0xozXo5dxh8Mt8u6Qsc8dZHXmPBeymtMeOFBdu1ZNR4YeIntggzs4RZbeU4TXKgI0dY_FmgLSBBCezkShADDJYi6EPKi_B7nFyAE2c8xVCUyXIAoyyLLS1dxEULAL0WE0LkNFSHaWsniSOe3TwT4MuwT4YXaJ2XJZHzJOo97cXwPq6_nuNT36PTh8JsvCqD7KE-lRIfXqFrQJS6_en9xDi09G1-w3pJP_2TcA7M4cNhSHvtJfRy4xeVlu1FSmlwCNcgzCbCnXLfU34P6KJ_pxjPmrByHGf1kCYmMYGGlqzHLGIF5fLMpnacKCAsCHrz-gdFrd_Jo9Oid-sozvNoEq-Fg4zfLYW_QWMWfH8rY6DOC9cHHS62gHaxlDsbr6z2iv1HRX3ZZmm_3Jm_u2zP74tMU3WD5GiCdAZ-m4JoaTlOwVpdXEZsnBhzVWxC4nZbESx5CnX26gzgeBwc4qTcvwjkM4dUybKWjeBwaIKVvUURzekyvkoFzEMMTpiVsxZUYPXkcs7Ql_Y3o58nm8_H5FmWmAT26DluZY39UzIwP96AvsHTVuWCYyb6eqDPU19UNbmUrHPCjw51royMtRX62OVlq8CZHWw6bsEKFoETpxy-TpQQLQpWbER8EozfERl9_GG1vRScPh0-PovvfjX-6NTl4aZ_V_K1Uyx-Hy9eWlgP0zRoDn9Oa3CVCemu0cGIU12Px45nj3RfRzmO7oMCmZ6kVttdtUoKkBbF2hvAnbqIXER_l0g_or3UCIiGEav5qzOm9YgXupPzBAEZ38E305Ws73XRC58GdyIfoMVspBZFOzbMn4w-AItiVBC1cabjPUl4U2UoKCG5awg9dUTrvt6YSvvIhSrq-Tja38blYwgqqZc7MCUHFPpyloOnzhBmBxf2wbgpHJ4kcYqeAi9FiWeSlertEi_qBOcfUCqEWT908pemT4fS95kDJdHeJxW8lPtVsp5vj6JY_DtqZjbbz9zLtpUjMkZxUp6tqkWtGCafva0ap6YBqWGWMMRnHnFfiEj7f9bgIH_iiE8NlcDpHmCTc2BKDOpGglJ5p1szgmUYtXOk2vd8n4cvpjsZrqG93NOqjvDua0uWL-XOMvt3RmLG0O5oxAqVpJaXzoU3nBDaoNNVeCktfPqA0raZoXrTpgsAGl6bam83Y5MwVR8B0nrSpyAeXprr7zGI--I6Q8Xk5_yjOTGjIVpczNj78cHF8Mjl9Oj58A_TRpXfTdXH2NhrF1WC96TNCTV7BcMB1VPhiM_oi5lIz3DrqR8yI6yi4hpYpGD18-fnkmSruQ796GLQkyji_ZBC2g_aAhK86vax_KluccpYLeCV_4TDqlTwcSQzMmsJe47d_GW__AIzFx7Kcofc6TWv8Czut4nYGpxVuUizypaOumUJflDqXSIhPYXDs3hUt7iTPdfI6yU5pgXKhluX069IilOBLyLU-LTqRYcJBeocJpGfnFqHMcQG51rvFpi4sF66a5fTu5iKkOa4f12WEsRHNY4FUL6nvbTWdvgZTJNLq4mCUlvdz2Wnns7RrPfyjIpxQXmXMichr4QB3c3UjXt6t6FplxaFPPn832QOsxrNZ9M719mWBr4TdQQ5wrU-Mvcu5Ts5nRxtnzPtenw3qWWRVescSVqDXQTe8XkilV9nkgeBRjbfJ_LB38etOMVWF7xmTGJWqKm5nUFW4iVtVpa8YllBDh7iSofaup3An4PE1uJgIZz71OJ2l9XxlLoGFi0etr4nCFhKPBNOveCSUYPGo9TrR97WQeCSQnsUjocwhHrX-JzZzi4hHwuldPBLSHOJR64lipLC3U0nq5b4faeaCzapOO7GZWyToRDB9q2P6fgLVsSkqQ4VhMZOK5RrEpMbtTMUFUBO3JtUYq6HUxUwq_qr-L8FBnTgwqZSzjEOG0Hq_CwfTlrepjLaQTSWcfm0qoSxtU9kLW8imEkjPNpVQOrCp8dQtYlMJp3ebSkgd2NSYtIjiJaS-qlzynKUsai1ZcQEpOCpCz5k4hNCQiQNwOySLbSHNQCB9awa6_uTQDLpq3fRqz2KaAXKZK9YM65pbXDnNsF74_ladZqjoTx0m2MVEA_6u3kUD7qSsaEiHt5CFIZyeLQzBzGFhtKcP6V28heQC4fQrFwhmabkQXzhcxIwSSO9mlHCWjWskpMCrw1XTthcG3scz7qOYWJiLKSeb28Ov_ho9y-3RJ6B9NKTLg468DpeG5U690MnL9VN4J85WXXIg7-J8f3jzqNA09lSfo6palX7fXu2UUhKU-eL8EMuIo9uj1_YrFGYUE7rVWWmsbvwh6Pevd3rui7FIU5v21uV6KxbUkPipnrKPuWJ9prnHRFety2UOnNtdeKhSV2ZIGPU3P0afA66mUlDTObgUfBIMAvev-Kxqwqd92UWHtqYDXdi2yD2SsDlfzUpLtIjjjLDudffTfVpeyVFHrB87tbbKAzPPedJqk4lHiX07ZwhtjmwZi9z6V4KUVjDgHpcmvukCshfHp8N7bycvD4YvzyVM9OPHnVYoo6a3MasoMwlveHcUf4zDNDcsyMLldZpAp1ul0J9Pvnvyb-g_aL7C6Kbpr5VsyrIO18PmsgaPK-tAPo5vW5z1xv8DEpoQxg";
        PhinxExtend::write2menu(CodeExtend::dezip($menu));
    }

    /**
     * 初始化配置参数
     * @return void
     * @throws \think\db\exception\DbException
     */
    private function insertConf()
    {
        // 检查数据
        if (SystemConfig::mk()->count()) return;

        // 写入数据
        SystemConfig::mk()->insertAll([
            ['type' => 'base', 'name' => 'app_name', 'value' => 'DeAdmin'],
            ['type' => 'base', 'name' => 'app_version', 'value' => 'v6'],
            ['type' => 'base', 'name' => 'editor', 'value' => 'ckeditor5'],
            ['type' => 'base', 'name' => 'login_name', 'value' => 'DeAdmin后台管理'],
            ['type' => 'base', 'name' => 'site_copy', 'value' => '©版权所有 2010-' . date('Y') . ' DeAdmin'],
            ['type' => 'base', 'name' => 'site_icon', 'value' => 'https://img.sqm.la/logo.png'],
            ['type' => 'base', 'name' => 'site_name', 'value' => 'DeAdmin'],
            ['type' => 'base', 'name' => 'site_theme', 'value' => 'default'],
            ['type' => 'wechat', 'name' => 'type', 'value' => 'api'],
            ['type' => 'storage', 'name' => 'type', 'value' => 'local'],
            ['type' => 'storage', 'name' => 'allow_exts', 'value' => 'doc,gif,ico,jpg,mp3,mp4,p12,pem,png,zip,rar,xls,xlsx'],
        ]);
    }

    /**
     * 初始化用户数据
     * @return void
     * @throws \think\db\exception\DbException
     */
    private function insertUser()
    {
        // 检查是否存在
        if (SystemUser::mk()->count()) return;

        // 初始化默认数据
        SystemUser::mk()->save([
            'id'       => '10000',
            'username' => 'superAdmin',
            'nickname' => '超级管理员',
            'password' => '21232f297a57a5a743894a0e4a801fc3',
            'headimg'  => 'https://img.sqm.la/image/d5/1870bda08f10dd2ea4f41540f08767.jpeg',
        ]);
    }
}