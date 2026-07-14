## Pour du code (PHP/CTF)

PARAMETER temperature 0.3 # Plus déterministe pour le code
PARAMETER repeat_penalty 1.3 # Évite les boucles dans les snippets

## Pour de la créativité (brainstorming)

PARAMETER temperature 0.9
PARAMETER top_p 0.95

## Format : Base vs. Instruct

### Modèles "base" (qwen2.5-coder:7b-base)
→ Format brut : Pas de finetuning pour suivre des instructions ou répondre à des questions.
→ Usage : Uniquement pour la génération de code (ex: autocomplétion dans un IDE).
→ Exemple de prompt :

```
# Écrire une fonction en Python pour trier une liste
def sort_list(lst):
```

→ Le modèle complétera le code sans explication.

### Modèles "instruct" (qwen2.5:7b-instruct-q4_K_M, qwen2.5-coder:7b-instruct-q4_K_M)
→ Finetunés pour suivre des instructions : Comprennent des requêtes en langage naturel (ex: "Corrige ce code PHP pour éviter les injections SQL").
→ Usage : Interaction conversationnelle (chat, Q&A, explications).
→ Exemple de prompt :

```
Explique-moi comment fonctionne cette vulnérabilité XSS dans ce code PHP :
<?php echo $_GET['input']; ?>
```

→ Le modèle expliquera la vulnérabilité et proposera une correction.

### Modèles "abliterated"

- https://huggingface.co/blog/mlabonne/abliteration
- https://ollama.com/huihui_ai/qwen2.5-abliterate:7b-instruct - 
- https://ollama.com/huihui_ai/qwen2.5-coder-abliterate - huihui_ai/qwen2.5-coder-abliterate:7b

## Qquantization

- https://ollama.com/library/qwen2.5-coder/tags

Sur Ollama, les tags officiels suivent cette convention :
...-q4_k_m → 4 bits (recommandé CPU/16-32Go RAM)
...-q5_k_m → 5 bits (si vous avez 64Go+ ou besoin de précision max)
...-q8_0 → 8 bits (poids presque entiers, ~10 Go, qualité maximale)
...-bf16 → Pleine précision (~14 Go, à éviter sur Ryzen 1600 sans SSD rapide)

| Partie | Signification                   | Impact technique                                                                             | 
|:-------|---------------------------------|----------------------------------------------------------------------------------------------|
| Q4     | Quantification en 4 bits        | Poids ~4× plus légers qu'en BF16 (14 Go → ~5 Go de RAM)                                      |
| _K     | K-quantization (llama.cpp/GGUF) | Mélange intelligemment les tailles de quantification par couches pour préserver la précision |
| _M     | Mixed / Middle                  | Variante équilibrée : meilleur compromis qualité/vitesse sur CPU/RAM limitée                 |

- qwen2.5-coder:7b et qwen2.5-coder:7b-base
    → Non quantifiés : Taille originale (~13 Go pour 7B).
    → VRAM requise : ~14-16 Go (pour 7B).
    → Précision : Meilleure qualité, mais plus gourmand en ressources.

- qwen2.5:7b-instruct-q4_K_M, qwen2.5-coder:7b-base-q4_K_M et qwen2.5-coder:7b-instruct-q4_K_M
    → Quantifiés en 4 bits (q4_K_M) : Réduction de la taille (~4.7 Go) et de la VRAM requise (~6-8 Go).
    → Précision : Légère perte de qualité (souvent imperceptible pour la plupart des tâches).
    → Avantage : Permet de faire tourner le modèle sur des GPU moins puissants (ex: RTX 3060 avec 12 Go de VRAM).

### Verifier la Quantization

```shell
ollama pull qwen2.5-coder:7b-q4_k_m
ollama create agent-coder -f Modelfile
ollama show agent-coder | grep quantization
```

> Retourne : quantization: Q4_K_M
