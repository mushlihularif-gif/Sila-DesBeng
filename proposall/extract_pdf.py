import PyPDF2

pdf_path = r"EGOV_KMIPN2026_Tim Gen Hello World_POLITEKNIK NEGERI BENGKALIS.pdf"

with open(pdf_path, 'rb') as f:
    reader = PyPDF2.PdfReader(f)
    total_pages = len(reader.pages)
    print(f"=== TOTAL HALAMAN PDF: {total_pages} ===\n")
    
    # Print pages 1-10
    for i in range(min(10, total_pages)):
        text = reader.pages[i].extract_text()
        print(f"--- HALAMAN PDF {i+1} ---")
        print(text[:3000])
        print()
