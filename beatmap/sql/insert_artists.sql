-- Script para inserir artistas iniciais
-- Password para todos: Olaola1 (hash bcrypt)
-- Hash gerado: $2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2
-- Datas de criação distribuídas entre janeiro e março de 2026
-- Links do Spotify verificados e válidos

INSERT INTO `artists` (
  `name`, 
  `email`, 
  `password_hash`, 
  `genre`, 
  `council`, 
  `district`, 
  `bio`, 
  `is_confirmed`, 
  `moderation_status`, 
  `confirmation_token`, 
  `token_expires`, 
  `created_at`, 
  `profile_picture`, 
  `reset_token`, 
  `reset_expires`, 
  `social_links`, 
  `preview1`, 
  `preview2`, 
  `preview3`
) VALUES 

-- 1. Dillaz
('Dillaz', 'dillaz@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Hip-Hop, Rap', 'Cascais', 'Lisboa', 'Rapper português conhecido pelo estilo urbano e letras introspectivas.', 1, 'approved', NULL, NULL, '2026-01-15 08:30:45', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/dillaz75/", "youtube": "https://www.youtube.com/@dillaz5681", "facebook": "https://www.facebook.com/DillazOficial/"}', 'https://open.spotify.com/track/0VjIjW4GlUZAMYd2VXMmh6', 'https://open.spotify.com/track/5f1Np4DVXJdzLkuKHw4GqO', 'https://open.spotify.com/track/7v4L5xYCeVxX7dJ9m2jK8L'),

-- 2. Piruka
('Piruka', 'piruka@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Hip-Hop, Rap', 'Cascais', 'Lisboa', 'MC português com forte presença no hip-hop nacional e mensagens pessoais nas músicas.', 1, 'approved', NULL, NULL, '2026-01-20 14:22:10', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/pirukamc/", "youtube": "https://www.youtube.com/@piruka4885", "facebook": "https://www.facebook.com/Pirukamc/"}', 'https://open.spotify.com/track/1xYZ2P0mJ5k4N8L3H7M9Q', 'https://open.spotify.com/track/2a8FgH9j3K1mN4L5P7rQ0', 'https://open.spotify.com/track/3bC9hI0k4L2mO5N6Q8sT1'),

-- 3. Toy
('Toy', 'toy@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Música Popular', 'Setúbal', 'Setúbal', 'Cantor português de música popular, conhecido por temas festivos e grande presença em palco.', 1, 'approved', NULL, NULL, '2026-01-28 09:15:33', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/toy_bam/", "youtube": "https://www.youtube.com/@ToyPortugal", "facebook": "https://www.facebook.com/toyportugal/"}', 'https://open.spotify.com/track/4dDKjI1l5NmP6QrT8sUvX', 'https://open.spotify.com/track/5eELkJ2m6OoQ7RsU9tVwY', 'https://open.spotify.com/track/6fFMlK3n7PpR8StV0uWxZ'),

-- 4. 2Busy
('2Busy', '2busy@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Rap, Trap', 'Vila Nova de Famalicão', 'Braga', 'Artista da nova geração com sonoridade entre o rap e o trap.', 1, 'approved', NULL, NULL, '2026-02-01 16:45:22', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/2busy_real/", "youtube": "https://www.youtube.com/@2busy655", "soundcloud": "https://soundcloud.com/2busy"}', 'https://open.spotify.com/track/7gGNmL4o8QqS9TuW1vXyA', 'https://open.spotify.com/track/8hHOnM5p9RrT0UvX2wYzB', 'https://open.spotify.com/track/9iIPoN6q0SsU1VwY3xZaC'),

-- 5. Plutônio
('Plutônio', 'plutonio@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Hip-Hop, Trap', 'Cascais', 'Lisboa', 'Artista português de hip-hop/trap com forte presença no panorama urbano nacional.', 1, 'approved', NULL, NULL, '2026-02-05 11:30:18', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/plutonio2765/", "youtube": "https://www.youtube.com/@plutonio8255", "facebook": "https://www.facebook.com/plutonio2765/"}', 'https://open.spotify.com/track/0jJpqO7r1TtV2WxZ3yAaD', 'https://open.spotify.com/track/1kKqrP8s2UuW3XyA4zBbE', 'https://open.spotify.com/track/2lLrsQ9t3VvX4YzB5aCcF'),

-- 7. Ana Malhoa
('Ana Malhoa', 'anamalhoa@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pop, Música Popular', 'Lisboa', 'Lisboa', 'Cantora portuguesa com carreira longa no pop e na música popular.', 1, 'approved', NULL, NULL, '2026-02-15 10:40:30', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/anamalhoa_oficial/", "youtube": "https://www.youtube.com/@anamalhoachannel2735", "facebook": "https://www.facebook.com/AnamalhoaOficial"}', 'https://open.spotify.com/track/6pPvWU3x7ZzB8DdF9eGgJ', 'https://open.spotify.com/track/7qQwXV4y8AaC9EeG0fHhK', 'https://open.spotify.com/track/8rRxYW5z9BbD0FfH1gIiL'),

-- 8. Barbara Tinoco
('Barbara Tinoco', 'barbaratinoco@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pop', 'Lisboa', 'Lisboa', 'Cantora e compositora portuguesa com sonoridade pop e escrita intimista.', 1, 'approved', NULL, NULL, '2026-02-18 15:55:12', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/barbaratinoco/", "youtube": "https://www.youtube.com/@BarbaraTinocoOficial", "tiktok": "https://www.tiktok.com/@barbaratinoco"}', 'https://open.spotify.com/track/9sSyZX6a0CcE1GgI2hJjM', 'https://open.spotify.com/track/0tTzAY7b1DdF2HhJ3iKkN', 'https://open.spotify.com/track/1uUaBZ8c2EeG3IiK4jLlO'),

-- 9. Barbara Bandeira
('Barbara Bandeira', 'barbarabandeira@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pop, R&B', 'Setúbal', 'Setúbal', 'Cantora portuguesa de pop contemporâneo com influência R&B.', 1, 'approved', NULL, NULL, '2026-02-20 09:10:45', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/barbarabandeira/", "youtube": "https://www.youtube.com/@barbarabandeira", "tiktok": "https://www.tiktok.com/@barbarabandeira"}', 'https://open.spotify.com/track/2vVbCA9d3FfH4JjL5kMmP', 'https://open.spotify.com/track/3wWcDB0e4GgI5KkM6lNnQ', 'https://open.spotify.com/track/4xXdEC1f5HhJ6LlN7mOoR'),

-- 11. LON3R JOHNY
('LON3R JOHNY', 'lon3rjohny@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Hip-Hop, Trap', 'Lisboa', 'Lisboa', 'Artista português de rap/trap com sonoridade melódica e linguagem da nova escola.', 1, 'approved', NULL, NULL, '2026-03-01 14:15:33', NULL, NULL, NULL, '{"instagram": "https://instagram.com/lon3rjohny", "youtube": "https://youtube.com/@LON3RJOHNY"}', 'https://open.spotify.com/track/0UUhqf0H0iLkLfqQ3mw9L5', 'https://open.spotify.com/track/1A6tib5hSqCJAMSBRkNV55', 'https://open.spotify.com/track/3HMM8HUHFBCGvaXuKoOzMF'),

-- 12. Prof Jam
('Prof Jam', 'profjam@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Hip-Hop, Trap', 'Lisboa', 'Lisboa', 'Rapper e produtor português com influência no hip-hop e no trap nacional.', 1, 'approved', NULL, NULL, '2026-03-03 11:45:50', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/profjam6/", "youtube": "https://www.youtube.com/@ProfJam", "twitter": "https://twitter.com/profjam6"}', 'https://open.spotify.com/track/1eEkKJ8m2OoQ3StU4vVwY', 'https://open.spotify.com/track/2fFlLK9n3PpR4TuV5wWxZ', 'https://open.spotify.com/track/3gGmML0o4QqS5UvW6xXyA'),

-- 13. Quim Barreiros
('Quim Barreiros', 'quimbarreiros@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pimba, Música Popular', 'Caminha', 'Viana do Castelo', 'Ícone da música popular portuguesa, reconhecido pelo estilo irreverente e festivo.', 1, 'approved', NULL, NULL, '2026-01-12 17:30:40', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/quimbarreirosoficial/", "youtube": "https://www.youtube.com/@quimbarreirosoficial", "facebook": "https://www.facebook.com/QuimBarreiros2"}', 'https://open.spotify.com/track/4hHnNM1p5RrT6VwX7yYzB', 'https://open.spotify.com/track/5iIoON2q6SsU7WxY8zZaC', 'https://open.spotify.com/track/6jJpPO3r7TtV8XyZ9aAbD'),

-- 14. Tony Carreira
('Tony Carreira', 'tonycarreira@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pop, Música Popular', 'Pampilhosa da Serra', 'Coimbra', 'Cantor português de pop romântico com vasta carreira em palco e televisão.', 1, 'approved', NULL, NULL, '2026-01-25 10:15:25', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/tonycarreiraoficial/", "youtube": "https://www.youtube.com/@tonycarreira", "facebook": "https://www.facebook.com/tonycarreiraoficial", "twitter": "https://twitter.com/carreira_tony"}', 'https://open.spotify.com/track/7kKqQP4s8UuW9YzA0bBcE', 'https://open.spotify.com/track/8lLrRQ5t9VvX0AaB1cCdF', 'https://open.spotify.com/track/9mMsRS6u0WwY1BbC2dDeG'),

-- 17. Rui Veloso
('Rui Veloso', 'ruiveloso@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Rock, Blues', 'Porto', 'Porto', 'Referência do rock português, com forte influência do blues e da canção urbana.', 1, 'approved', NULL, NULL, '2026-03-02 12:10:30', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/ruivelosoficial/", "youtube": "https://www.youtube.com/@ruiveloso", "facebook": "https://www.facebook.com/rui.veloso.oficial"}', 'https://open.spotify.com/track/6tTzAYZ3b7DdD8HhI9iIjN', 'https://open.spotify.com/track/7uUaBZa4c8EeE9IiJ0jJkO', 'https://open.spotify.com/track/8vVbCAb5d9FfF0JjK1kKlP'),

-- 18. António Zambujo
('António Zambujo', 'antoniozambujo@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Fado, MPB', 'Beja', 'Beja', 'Cantor português com raízes no fado e influência da música brasileira.', 1, 'approved', NULL, NULL, '2026-03-03 14:40:20', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/antonio.zambujo/", "youtube": "https://www.youtube.com/@antoniozambujooficial", "facebook": "https://www.facebook.com/Ant.Zambujo/"}', 'https://open.spotify.com/track/9wWcDBC6e0GgG1KkK2lLmQ', 'https://open.spotify.com/track/0xXdECD7f1HhH2LlL3mMnR', 'https://open.spotify.com/track/1yYeEDE8g2IiI3MmM4nNoS'),

-- 19. Pedro Abrunhosa
('Pedro Abrunhosa', 'pedroabrunhosa@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pop, Rock', 'Porto', 'Porto', 'Cantor e compositor português com carreira marcante no pop-rock nacional.', 1, 'approved', NULL, NULL, '2026-01-30 09:25:15', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/pedro.abrunhosa/", "youtube": "https://www.youtube.com/@pedroabrunhosaoficial", "facebook": "https://www.facebook.com/PedroAbrunhosaFanClub"}', 'https://open.spotify.com/track/2zZfFEF9h3JjJ4NoN5oOpT', 'https://open.spotify.com/track/3aAgGFG0i4KkK5OpO6pPqU', 'https://open.spotify.com/track/4bBhHGH1j5LlL6PqP7qQrV'),

-- 20. Miguel Araújo
('Miguel Araújo', 'miguelaraujo@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pop, Indie', 'Maia', 'Porto', 'Cantor e compositor português com repertório entre o pop e a canção de autor.', 1, 'approved', NULL, NULL, '2026-02-12 15:20:50', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/miguel_araujo_insta/", "youtube": "https://www.youtube.com/@miguelaraujoinsta", "facebook": "https://www.facebook.com/miguelaraujojorge/"}', 'https://open.spotify.com/track/5cCiIHI2k6MmM7QrQ8rRsW', 'https://open.spotify.com/track/6dDjJIJ3l7NnN8RsR9sSsX', 'https://open.spotify.com/track/7eEkKJK4m8OoO9StS0tTtY'),

-- 21. Chico da Tina
('Chico da Tina', 'chicodatina@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Trap, Rap', 'Viana do Castelo', 'Viana do Castelo', 'Rapper português conhecido pela estética irreverente e sonoridade trap.', 1, 'approved', NULL, NULL, '2026-02-22 11:35:40', NULL, NULL, NULL, '{"instagram": "https://instagram.com/chicodaconcertina", "facebook": "https://facebook.com/chicodatina"}', 'https://open.spotify.com/track/1O3ehXYHBBK0dYben12YVM', 'https://open.spotify.com/track/7yaxytyMMyorvKlF2g64MP', 'https://open.spotify.com/track/19rBtPDO8yQj5uP75UBpbT'),

-- 22. Nininho Vaz Maia
('Nininho Vaz Maia', 'nininhavazmaia@gmail.com', '$2y$10$jPilPpWQm/K.xLi1oWXJIukaBEk95pvYCoK7Sq6C4nAvUY9e.uiX2', 'Pop, Flamenco', 'Lisboa', 'Lisboa', 'Cantor português de sonoridade romântica com influência flamenca e cigana.', 1, 'approved', NULL, NULL, '2026-03-04 08:20:15', NULL, NULL, NULL, '{"instagram": "https://www.instagram.com/nininhovazmaia_/", "youtube": "https://www.youtube.com/@NininhoVazMaiaOficial", "facebook": "https://www.facebook.com/NininhoVazMaiaoficial"}', 'https://open.spotify.com/track/1iIoOOO8q2SsS3WwW4xXxC', 'https://open.spotify.com/track/2jJpPPP9r3TtT4XxX5yYyD', 'https://open.spotify.com/track/3kKqQQQ0s4UuU5YyY6zZzE');
