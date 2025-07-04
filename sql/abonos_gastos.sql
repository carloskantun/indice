CREATE TABLE abonos_gastos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  gasto_id INT,
  monto DECIMAL(10,2),
  fecha DATE,
  comentario TEXT,
  archivo_comprobante VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (gasto_id) REFERENCES gastos(id)
);
