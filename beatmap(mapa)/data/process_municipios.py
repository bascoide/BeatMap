# Corrige os nomes dos municípios nos ficheiros gerados
import json
import geopandas as gpd

# 1. Corrigir o GeoJSON
print("🔧 Corrigindo nomes no GeoJSON...")
gdf = gpd.read_file("data/municipios_pt.geojson")

# Verificar se temos a coluna 'municipio'
if 'municipio' in gdf.columns:
    print(f"✅ Coluna 'municipio' encontrada com {len(gdf['municipio'].unique())} nomes únicos")
    
    # Renomear 'dtmn' para 'codigo_dtmn'
    if 'dtmn' in gdf.columns:
        gdf = gdf.rename(columns={'dtmn': 'codigo_dtmn'})
        print("✅ 'dtmn' renomeado para 'codigo_dtmn'")
    
    # Salvar corrigido
    gdf.to_file("data/municipios_pt_corrigido.geojson", driver='GeoJSON')
    print("✅ GeoJSON corrigido salvo: municipios_pt_corrigido.geojson")
    
    # Mostrar alguns exemplos
    print("\n📝 Exemplos de municípios:")
    for i in range(min(5, len(gdf))):
        print(f"  {gdf['codigo_dtmn'].iloc[i]}: {gdf['municipio'].iloc[i]} ({gdf['distrito_ilha'].iloc[i]})")

# 2. Corrigir o índice JSON
print("\n📋 Corrigindo índice JSON...")
with open("data/municipios_index.json", "r", encoding="utf-8") as f:
    index_data = json.load(f)

# Ler o GeoJSON para obter os nomes corretos
if 'municipio' in gdf.columns:
    # Criar dicionário de mapeamento código->nome
    codigo_para_nome = dict(zip(gdf['codigo_dtmn'], gdf['municipio']))
    distrito_para_codigo = dict(zip(gdf['codigo_dtmn'], gdf['distrito_ilha']))
    
    # Atualizar cada entrada no índice
    for item in index_data:
        codigo = item.get('nome')  # Atualmente tem o código
        if codigo in codigo_para_nome:
            item['nome'] = codigo_para_nome[codigo]
            item['codigo_dtmn'] = codigo
            item['distrito'] = distrito_para_codigo.get(codigo, '')
    
    # Salvar corrigido
    with open("data/municipios_index_corrigido.json", "w", encoding="utf-8") as f:
        json.dump(index_data, f, ensure_ascii=False, indent=2)
    
    print("✅ Índice corrigido salvo: municipios_index_corrigido.json")
    print(f"📊 Total de entradas: {len(index_data)}")

print("\n✅ CORREÇÃO COMPLETA!")