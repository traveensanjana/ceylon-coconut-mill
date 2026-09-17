CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(100) NOT NULL,
  password VARCHAR(100) NOT NULL,
  role VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(10) NOT NULL,
  name VARCHAR(100) NOT NULL,
  product VARCHAR(100) NOT NULL,
  quantity INT NOT NULL,
  amount INT NOT NULL,
  status VARCHAR(20) NOT NULL
);

INSERT INTO users (name, username, password, role) VALUES
('Administrator', 'Admin', 'admin123', 'Admin'),
('Kamal Perera', 'kamal', 'kamal123', 'Staff'),
('Nimal Silva', 'nimal', 'nimal123', 'Staff');
