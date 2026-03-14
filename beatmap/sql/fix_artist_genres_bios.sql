-- Corrige géneros e biografias de artistas já existentes na BD
-- Executar em MariaDB/MySQL na base de dados `beatmap`

UPDATE artists SET genre = 'Hip-Hop, Rap', bio = 'Rapper português conhecido pelo estilo urbano e letras introspectivas.' WHERE email = 'dillaz@gmail.com';
UPDATE artists SET genre = 'Hip-Hop, Rap', bio = 'MC português com forte presença no hip-hop nacional e mensagens pessoais nas músicas.' WHERE email = 'piruka@gmail.com';
UPDATE artists SET genre = 'Música Popular', bio = 'Cantor português de música popular, conhecido por temas festivos e grande presença em palco.' WHERE email = 'toy@gmail.com';
UPDATE artists SET genre = 'Rap, Trap', bio = 'Artista da nova geração com sonoridade entre o rap e o trap.' WHERE email = '2busy@gmail.com';
UPDATE artists SET genre = 'Hip-Hop, Trap', bio = 'Artista português de hip-hop/trap com forte presença no panorama urbano nacional.' WHERE email = 'plutonio@gmail.com';
UPDATE artists SET genre = 'Pop, Música Popular', bio = 'Cantora portuguesa com carreira longa no pop e na música popular.' WHERE email = 'anamalhoa@gmail.com';
UPDATE artists SET genre = 'Pop', bio = 'Cantora e compositora portuguesa com sonoridade pop e escrita intimista.' WHERE email = 'barbaratinoco@gmail.com';
UPDATE artists SET genre = 'Pop, R&B', bio = 'Cantora portuguesa de pop contemporâneo com influência R&B.' WHERE email = 'barbarabandeira@gmail.com';
UPDATE artists SET genre = 'Hip-Hop, Trap', bio = 'Artista português de rap/trap com sonoridade melódica e linguagem da nova escola.' WHERE email = 'lon3rjohny@gmail.com';
UPDATE artists SET genre = 'Hip-Hop, Trap', bio = 'Rapper e produtor português com influência no hip-hop e no trap nacional.' WHERE email = 'profjam@gmail.com';
UPDATE artists SET genre = 'Pimba, Música Popular', bio = 'Ícone da música popular portuguesa, reconhecido pelo estilo irreverente e festivo.' WHERE email = 'quimbarreiros@gmail.com';
UPDATE artists SET genre = 'Pop, Música Popular', bio = 'Cantor português de pop romântico com vasta carreira em palco e televisão.' WHERE email = 'tonycarreira@gmail.com';
UPDATE artists SET genre = 'Rock, Blues', bio = 'Referência do rock português, com forte influência do blues e da canção urbana.' WHERE email = 'ruiveloso@gmail.com';
UPDATE artists SET genre = 'Fado, MPB', bio = 'Cantor português com raízes no fado e influência da música brasileira.' WHERE email = 'antoniozambujo@gmail.com';
UPDATE artists SET genre = 'Pop, Rock', bio = 'Cantor e compositor português com carreira marcante no pop-rock nacional.' WHERE email = 'pedroabrunhosa@gmail.com';
UPDATE artists SET genre = 'Pop, Indie', bio = 'Cantor e compositor português com repertório entre o pop e a canção de autor.' WHERE email = 'miguelaraujo@gmail.com';
UPDATE artists SET genre = 'Trap, Rap', bio = 'Rapper português conhecido pela estética irreverente e sonoridade trap.' WHERE email = 'chicodatina@gmail.com';
UPDATE artists SET genre = 'Pop, Flamenco', bio = 'Cantor português de sonoridade romântica com influência flamenca e cigana.' WHERE email = 'nininhavazmaia@gmail.com';
