from PyPDF2 import PdfReader
import document
from document import document
# Este script converte um arquivo PDF em um arquivo DOCX usando PyPDF2 e python-docx.

# Caminho do arquivo de entrada e saída
pdf_path = "Contrato de Prestação de Serviços de Suporte Tecnológico - Plano Básico Revisado.pdf"
docx_path = "Contrato de Prestação de Serviços Plano Básico .docx"

# Criar um novo documento do Word
doc = document()

# Ler o PDF
reader = PdfReader(pdf_path)
for page in reader.pages:
    text = page.extract_text()
    if text:
        doc.add_paragraph(text)

# Salvar como DOCX
doc.save(docx_path)

# Retornar o caminho do arquivo convertido
docx_path