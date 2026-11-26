"""Build the Ledger catalog from a ForeverTome JSON export and Timesaver profession pages.

Run from the repository root:
python3 scripts/import-wow-recipes.py SNAPSHOT_JSON DIRECTORY_WITH_PROFESSION_HTML
Download the JSON snapshot from https://forevertome.com/professions.
Save pages from https://timesaver.gg/tools/wow-forever/professions/
as leatherworking.html, cooking.html and first-aid.html.
Optionally add legacy-leatherworking.html, legacy-cooking.html and legacy-first-aid.html
from https://legacyofwarcraft.com/db/forever/professions/index.html
to fill in acquisition categories missing from Timesaver.
"""
import html
import json
import re
import sys
from pathlib import Path

PROFESSIONS = {"Leatherworking": "leatherworking", "Cooking": "cooking", "First Aid": "first-aid"}


def sources(path):
    rows = {}
    for row in re.findall(r"<li>(.*?)</li>", path.read_text(), re.DOTALL):
        match = re.search(
            r'wowhead\.com/forever/spell=(\d+)"[^>]*>.*?</a></span>'
            r'<span><span class="o">(\d+)</span>(.*?)</span><span>[^<]*</span><span>(.*)</span>$',
            row,
            re.DOTALL,
        )
        if not match:
            continue
        spell, skill, thresholds, source = match.groups()
        colors = {color: int(value) for color, value in re.findall(r'class="([ygx])">\s*(\d+)', thresholds)}
        source = re.sub(r'<span aria-hidden="true".*?(?=<span class="sr-only">)', '', source)
        source = html.unescape(re.sub(r"<[^>]+>", "", source))
        rows[int(spell)] = (int(skill), colors, " ".join(source.split()))
    if not rows:
        raise ValueError(f"No recipe source rows found in {path}")
    return rows


def legacy_sources(path):
    if not path.is_file():
        return {}
    rows = {}
    for spell, cells in re.findall(r'<tr id="r(\d+)">(.*?)</tr>', path.read_text(), re.DOTALL):
        fields = re.findall(r'<td[^>]*>(.*?)</td>', cells, re.DOTALL)
        if len(fields) < 5:
            continue
        category = html.unescape(re.sub(r"<[^>]+>", "", fields[4]).strip())
        if category in {"Trainer", "Starter", "Recipe"}:
            rows[int(spell)] = category
    return rows


def item_name(items, item_id):
    return items.get(item_id, {}).get("name") or f"Item #{item_id}"


def build_recipe(recipe, items, acquisition, fallback):
    spell = recipe["id"]
    skill, colors, source = acquisition.get(spell, (None, {}, None))
    if not source or source == "Source not listed yet":
        source = {
            "Trainer": "Trainer listing · Exact trainer unverified",
            "Starter": "Starter recipe listing · Live availability unverified",
            "Recipe": "Recipe item listing · Acquisition location unverified",
        }.get(fallback.get(spell))
    materials = [
        f'{item_name(items, reagent["itemId"])} ×{reagent["quantity"]}'
        for reagent in recipe["reagents"]
    ]
    outputs = []
    for output in recipe["outputs"]:
        item_id = output["itemId"]
        item = items.get(item_id, {})
        low, high = output["quantityMin"], output["quantityMax"]
        outputs.append({
            "name": item_name(items, item_id), "id": item_id,
            "quantity": str(low) if low == high else f"{low}–{high}",
            "level": item.get("requiredLevel"), "item_level": item.get("itemLevel"),
        })
    return {
        "id": spell, "name": recipe.get("name") or f"Spell #{spell}",
        "category": recipe.get("category"), "skill": skill,
        "yellow": colors.get("y", recipe.get("skillUpStart")),
        "green": colors.get("g"), "gray": colors.get("x", recipe.get("skillTrivial")),
        "source": source,
        "recipe_items": [
            {"id": item_id, "name": item_name(items, item_id), "level": items.get(item_id, {}).get("requiredLevel")}
            for item_id in recipe["recipeItemIds"]
        ],
        "materials": materials if recipe["reagentsKnown"] or materials else None,
        "outputs": outputs, "tools": recipe["tools"],
        "cooldown": recipe.get("cooldownSeconds") or None,
        "partial": recipe.get("dataStatus") != "complete" or not recipe["reagentsKnown"],
    }


def main():
    snapshot_path, source_directory = map(Path, sys.argv[1:])
    snapshot = json.loads(snapshot_path.read_text())
    items = {item["id"]: item for item in snapshot["items"]}
    catalog = {}
    for profession in snapshot["professions"]:
        name = profession["name"]
        if name not in PROFESSIONS:
            continue
        acquisition = sources(source_directory / f"{PROFESSIONS[name]}.html")
        fallback = legacy_sources(source_directory / f"legacy-{PROFESSIONS[name]}.html")
        recipes = [
            build_recipe(recipe, items, acquisition, fallback)
            for recipe in snapshot["recipes"] if profession["id"] in recipe["professionIds"]
        ]
        catalog[name] = sorted(recipes, key=lambda r: (r["skill"] is None, r["skill"] or 0, r["name"].casefold(), r["id"]))
        print(f'{name}: {len(recipes)} records, {sum(bool(r["source"]) for r in recipes)} with source entries')
    if set(catalog) != set(PROFESSIONS) or min(map(len, catalog.values())) < 25:
        raise ValueError("The snapshot is missing a profession or has incomplete coverage")
    target = Path("resources/data/wow-profession-catalog.json")
    temporary = target.with_suffix(".tmp")
    with temporary.open("w") as output:
        output.write('{"build": ' + json.dumps(snapshot["build"]) + ', "professions": {\n')
        for index, (profession, recipes) in enumerate(catalog.items()):
            if index:
                output.write(",\n")
            output.write("  " + json.dumps(profession) + ": [\n")
            output.write(",\n".join("    " + json.dumps(row, ensure_ascii=False) for row in recipes))
            output.write("\n  ]")
        output.write("\n}}\n")
    temporary.replace(target)


if __name__ == "__main__":
    main()
