import codecs

filepath = "D:/laragon/www/SilaDesBeng/resources/views/admin/layouts/admin.blade.php"
with codecs.open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Find the Gas Sidebar menu
gas_menu = """                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('admin.gas.index') ? 'active' : '' }}">
                                <a href="{{ route('admin.gas.index') }}" class="menu-link">
                                    <div data-i18n="Daftar Produk">Daftar Produk</div>
                                </a>
                            </li>"""
gas_new = """                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('admin.gas.index') ? 'active' : '' }}">
                                <a href="{{ route('admin.gas.index') }}" class="menu-link">
                                    <div data-i18n="Daftar Produk">Daftar Produk</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('admin.gas.kk.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.gas.kk.index') }}" class="menu-link">
                                    <div data-i18n="Verifikasi KK">Verifikasi KK (Krisis)</div>
                                </a>
                            </li>"""
content = content.replace(gas_menu, gas_new)

with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Sidebar updated.")
