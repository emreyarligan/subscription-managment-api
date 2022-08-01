

## API Reference
Postman collection available in the repo (subscription-managment-api.postman_collection.json)
#### Register a device


Only one device can be registered. Even if you send a different app_id, the old one is updated.
```http
  PUT /api/register
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `device_uuid` | `uuid` | **Required, integer** |
| `app_id` | `integer` | **Required, integer** |
| `language` | `string` | **Required, 'en' or 'tr'** |
| `os` | `string` | **Required, 'ios' or 'android'** |

#### Purchase

```http
  PUT /api/purchase
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `receiptId`      | `string` | **Required, integer**. |
| `clientToken`      | `string` | **Required,uuid**. It comes with Register enpoint |


#### Check Subscription

```http
  GET /api/check-subscription
```

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `clientToken`      | `string` | **Required,uuid**. It comes with Register enpoint |

## Worker / Subscription Queue
```bash
  get: /worker/prepare-queue
```
```bash
   php artisan queue:work --queue=subscriptionPolling
```


## Sequence Diagram

![image](https://user-images.githubusercontent.com/26210131/182005023-1e98d126-25c3-4c60-b25e-f94f427f6f9a.png)

## DB Diagram

![image](https://user-images.githubusercontent.com/26210131/182055150-16cebed3-59a0-4668-8ad6-6d93fc2ba363.png)
