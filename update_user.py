import codecs

filepath = "D:/laragon/www/SilaDesBeng/app/Models/User.php"
with codecs.open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

rel = """    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function familyMember()
    {
        return $this->hasOne(FamilyMember::class, 'nik_hash', 'nik_hash');
    }"""
content = content.replace("    public function region()\n    {\n        return $this->belongsTo(Region::class);\n    }", rel)

with codecs.open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
print("User model updated.")
