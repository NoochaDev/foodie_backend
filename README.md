# Laravel Project Setup

## Установка

```bash
git clone https://github.com/NoochaDev/foodie_backend.git
cd your-project
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

## Составление меню на неделю через Postman

- Запуск сервера
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

- Вписываем эндпоинт api в url-строку
```
http://your-ip-address/api/v1/getFitRecipes
```

- Пример ответа
```json
{
    "message": "Меню на неделю успешно сгенерировано",
    "week": {
        "1": {
            "breakfast": [
                {
                    "id": 7,
                    "title": null,
                    "meal_type_id": 1,
                    "nutrients": {
                        "protein": 22.398,
                        "fat": 13.878,
                        "carbohydrates": 14.322,
                        "calories": 271.782
                    }
                },
                {
                    "id": 9,
                    "title": null,
                    "meal_type_id": 1,
                    "nutrients": {
                        "protein": 7.57,
                        "fat": 1.395,
                        "carbohydrates": 38.7,
                        "calories": 197.63500000000002
                    }
                }
            ],
            "lunch": [
                {
                    "id": 14,
                    "title": null,
                    "meal_type_id": 2,
                    "nutrients": {
                        "protein": 17.932000000000002,
                        "fat": 4.038,
                        "carbohydrates": 109.7,
                        "calories": 546.87
                    }
                },
                {
                    "id": 11,
                    "title": null,
                    "meal_type_id": 2,
                    "nutrients": {
                        "protein": 45.241,
                        "fat": 32.3,
                        "carbohydrates": 21.5,
                        "calories": 557.664
                    }
                }
            ],
            "dinner": [
                {
                    "id": 25,
                    "title": null,
                    "meal_type_id": 3,
                    "nutrients": {
                        "protein": 28.32,
                        "fat": 20.91,
                        "carbohydrates": 3.117,
                        "calories": 313.93800000000005
                    }
                },
                {
                    "id": 33,
                    "title": null,
                    "meal_type_id": 3,
                    "nutrients": {
                        "protein": 13.238,
                        "fat": 2.441,
                        "carbohydrates": 71.19,
                        "calories": 359.681
                    }
                }
            ],
            "totals": {
                "calories": 2247.57,
                "protein": 134.699,
                "fat": 74.962,
                "carbohydrates": 258.529
            }
        },
        "2": {
            "breakfast": [
                {
                    "id": 7,
                    "title": null,
                    "meal_type_id": 1,
                    "nutrients": {
                        "protein": 22.398,
                        "fat": 13.878,
                        "carbohydrates": 14.322,
                        "calories": 271.782
                    }
                },
                {
                    "id": 9,
                    "title": null,
                    "meal_type_id": 1,
                    "nutrients": {
                        "protein": 7.57,
                        "fat": 1.395,
                        "carbohydrates": 38.7,
                        "calories": 197.63500000000002
                    }
                }
            ],
            "lunch": [
                {
                    "id": 14,
                    "title": null,
                    "meal_type_id": 2,
                    "nutrients": {
                        "protein": 17.932000000000002,
                        "fat": 4.038,
                        "carbohydrates": 109.7,
                        "calories": 546.87
                    }
                },
                {
                    "id": 11,
                    "title": null,
                    "meal_type_id": 2,
                    "nutrients": {
                        "protein": 45.241,
                        "fat": 32.3,
                        "carbohydrates": 21.5,
                        "calories": 557.664
                    }
                }
            ],
            "dinner": [
                {
                    "id": 33,
                    "title": null,
                    "meal_type_id": 3,
                    "nutrients": {
                        "protein": 13.238,
                        "fat": 2.441,
                        "carbohydrates": 71.19,
                        "calories": 359.681
                    }
                },
                {
                    "id": 25,
                    "title": null,
                    "meal_type_id": 3,
                    "nutrients": {
                        "protein": 28.32,
                        "fat": 20.91,
                        "carbohydrates": 3.117,
                        "calories": 313.93800000000005
                    }
                }
            ],
            "totals": {
                "calories": 2247.57,
                "protein": 134.699,
                "fat": 74.962,
                "carbohydrates": 258.529
            }
        },
...
}
```
