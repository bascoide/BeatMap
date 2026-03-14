# Processa os distritos a partir do ficheiro GPKG e corrige os nomes
import json
import geopandas as gpd

# 1. Ler e processar o GPKG para distritos
print("🔧 Processando distritos do GPKG...")
try:
    # Listar camadas disponíveis no GPKG
    layers = gpd.list_layers("data/caop_raw/Continente_CAOP2024_1.gpkg")
    print(f"📂 Camadas disponíveis: {list(layers['name'])}")
    
    # Procurar a camada de distritos com flexibilidade
    layer_name = None
    possible_names = ["cont_distritos", "Distritos", "distritos", "DISTRITOS", "districts", "Districts"]
    
    for name in possible_names:
        if name in layers['name'].values:
            layer_name = name
            print(f"✅ Camada encontrada: '{layer_name}'")
            break
    
    if layer_name is None:
        print(f"⚠️ Nenhuma camada de distritos encontrada!")
        print(f"   Camadas disponíveis: {list(layers['name'].values)}")
        print(f"   Procurar manualmente qual tem os distritos...")
        # Tenta usar a primeira camada que pareça ser de distritos
        for name in layers['name'].values:
            if 'dist' in name.lower():
                layer_name = name
                print(f"✅ Usando camada: '{layer_name}'")
                break
    
    if layer_name is None:
        print("❌ Impossível encontrar a camada de distritos. Abortando.")
        exit(1)
    
    gdf = gpd.read_file("data/caop_raw/Continente_CAOP2024_1.gpkg", layer=layer_name)
    print(f"✅ Dados de distritos carregados: {len(gdf)} registos")
    
    # Verificar colunas disponíveis
    print(f"📊 Colunas disponíveis: {list(gdf.columns)}")
    
    # Procurar coluna de nome do distrito com flexibilidade
    nome_col = None
    for col in ['distrito', 'Distrito', 'DISTRITO', 'name', 'Name', 'NAME']:
        if col in gdf.columns:
            nome_col = col
            break
    
    if nome_col is None:
        print("❌ Coluna de nome de distrito não encontrada.")
        print(f"   Colunas disponíveis: {list(gdf.columns)}")
        exit(1)
    
    # Procurar coluna de código com flexibilidade
    codigo_col = None
    for col in ['codigo', 'codigo_distrito', 'Codigo', 'CODIGO', 'code', 'Code', 'CODE', 'id', 'ID']:
        if col in gdf.columns:
            codigo_col = col
            break
    
    # Se tem coluna de código, renomear
    if codigo_col and codigo_col != 'codigo_distrito':
        gdf = gdf.rename(columns={codigo_col: 'codigo_distrito'})
        print(f"✅ Coluna '{codigo_col}' renomeada para 'codigo_distrito'")
    
    # Se não tem coluna de código, usar índice
    if 'codigo_distrito' not in gdf.columns:
        gdf['codigo_distrito'] = range(len(gdf))
        print("✅ Coluna 'codigo_distrito' criada com índice")
    
    # Renomear coluna de nome se necessário
    if nome_col != 'distrito':
        gdf = gdf.rename(columns={nome_col: 'distrito'})
        print(f"✅ Coluna '{nome_col}' renomeada para 'distrito'")
    
    # Salvar como GeoJSON
    gdf.to_file("data/distritos_pt.geojson", driver='GeoJSON')
    print("✅ GeoJSON de distritos salvo: distritos_pt.geojson")
    
    # Mostrar alguns exemplos
    print("\n📝 Exemplos de distritos:")
    for i in range(min(5, len(gdf))):
        codigo = gdf['codigo_distrito'].iloc[i]
        nome = gdf['distrito'].iloc[i]
        print(f"  {codigo}: {nome}")

except Exception as e:
    print(f"❌ Erro ao processar GPKG: {e}")
    import traceback
    traceback.print_exc()
    exit(1)

# 2. Criar índice JSON para distritos
print("\n📋 Criando índice JSON para distritos...")
index_data = []

for idx, row in gdf.iterrows():
    codigo = row.get('codigo_distrito', str(idx))
    nome = row['distrito']
    item = {
        "nome": nome,
        "codigo_distrito": str(codigo)
    }
    index_data.append(item)

# Salvar índice
with open("data/distritos_index.json", "w", encoding="utf-8") as f:
    json.dump(index_data, f, ensure_ascii=False, indent=2)

print("✅ Índice de distritos salvo: distritos_index.json")
print(f"📊 Total de distritos: {len(index_data)}")

print("\n✅ PROCESSAMENTO DE DISTRITOS COMPLETO!")