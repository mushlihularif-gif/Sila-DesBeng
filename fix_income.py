import re
import codecs

filepath = "D:/laragon/www/SilaDesBeng/app/Http/Controllers/Admin/ReportController.php"
with codecs.open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Add getActivatedServices call
old_start = """    public function income(Request $request)
    {
        // Dapatkan tahun yang dipilih (default ke tahun sekarang)"""
new_start = """    public function income(Request $request)
    {
        $activeServices = $this->getActivatedServices();
        // Dapatkan tahun yang dipilih (default ke tahun sekarang)"""
content = content.replace(old_start, new_start)

# Add to compact
old_compact = """            'unitPopulerData',
            'growth',
            'availableYears'
        ));"""
new_compact = """            'unitPopulerData',
            'growth',
            'availableYears',
            'activeServices'
        ));"""
content = content.replace(old_compact, new_compact)

with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
print("Fixed ReportController to pass activeServices to income view!")
