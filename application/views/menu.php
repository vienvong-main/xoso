<div class="container">
    <nav class="navbar navbar-static-top navbar-inverse">
        <div class="container-fluid">
            <div class="navbar-header">
                <button class="navbar-toggle collapsed" aria-controls="navbar" aria-expanded="false"
                        data-target="#navbar"
                        data-toggle="collapse" type="button">
                    <span class="sr-only">Menu</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a id="logo-text" class="navbar-brand" href="/">Ketquaxs24h</a>
                <span id="logo-text-sub" class="navbar-brand">.com</span>
            </div>
            <div id="navbar" class="collapse navbar-collapse navbar-right">
                <ul class="nav navbar-nav">
                    <li class="<?php if($this->uri->segment(1) == "hom-nay" || $this->uri->segment(1) == "") echo "active";?>">
                        <a href="/hom-nay">Hôm nay </a>
                    </li>

                    <li class="<?php if($this->uri->segment(1) == "tien-ich") echo "active";?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                           aria-haspopup="true" aria-expanded="true">Tiện ích <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="/tien-ich/lich-xs">Lịch XS</a></li>
                            <li class="dropdown dropdown-submenu"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Quay Thử XS</a>
                                <ul class="dropdown-menu">
                                    <li><a href="/tien-ich/quay-thu/mb">Miền Bắc</a></li>
                                    <li><a href="/tien-ich/quay-thu/mt">Miền Trung</a></li>
                                    <li><a href="/tien-ich/quay-thu/mn">Miền Nam</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <li class="<?php if($this->uri->segment(1) == "sokq") echo "active";?>">
                        <a href="/sokq">Sổ KQ</a>
                    </li>

                    <li>
                        <a href="/tien-ich/thong-ke">Thống Kê</a>
                    </li>

                    <li class="<?php if($this->uri->segment(1) == "ket-qua-bong-da") echo "active";?>">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                           aria-haspopup="true" aria-expanded="true">KQ Bóng đá <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="/ket-qua-bong-da">Bóng đá</a></li>
                            <li><a href="/ket-qua-bong-da/anh">Bóng đá Anh</a></li>
                            <li><a href="/ket-qua-bong-da/y">Bóng đá Ý</a></li>
                            <li><a href="/ket-qua-bong-da/tbn">Bóng đá TBN</a></li>
                            <li><a href="/ket-qua-bong-da/ca">Bóng đá Châu Âu</a></li>
                            <li><a href="/ket-qua-bong-da/duc">Bóng đá Đức</a></li>
                            <li><a href="/ket-qua-bong-da/phap">Bóng đá Pháp</a></li>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div>