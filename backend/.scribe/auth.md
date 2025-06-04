# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {votre-token}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

## Authentification

Cette API utilise l'authentification **Bearer Token** via Laravel Sanctum.

### Token de démonstration
Pour tester rapidement l'API, vous pouvez utiliser ce token de démonstration :
```
Bearer 2|QPzKqmaXBMlxX5yFf8JwNNdJEEHSpeum57Tb536R45e4fe14
```

### Comment obtenir votre propre token :
1. Connectez-vous à votre compte via l'endpoint `/api/login`
2. Récupérez le token retourné dans la réponse
3. Utilisez ce token dans l'en-tête `Authorization` de vos requêtes

### Comment utiliser votre token :
- **En-tête HTTP :** `Authorization: Bearer {votre-token}`
- **Dans "Try It Out" :** Cliquez sur le bouton "Authorize", puis saisissez `Bearer {votre-token}`

⚠️ **Important :** N'oubliez pas d'inclure le mot "Bearer" avant votre token !
