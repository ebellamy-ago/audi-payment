<?php

namespace AppBundle;

use Swagger\Annotations as SWG;

/**
 * Class SwaggerDefinitions
 * @package AppBundle
 */
class SwaggerDefinitions
{
    /**
     * @SWG\Swagger(
     *     @SWG\Info(
     *         version="1.0.0",
     *         title="Payment Api"
     *     ),
     *     host="api5.audi.agence-one.net",
     *     basePath="/payment/",
     *     schemes={"http"},
     *     produces={"application/json"},
     *
     *     @SWG\Definition(
     *         definition="Context",
     *         type="object",
     *         @SWG\Property(property="context", type="string")
     *     ),
     *
     *     @SWG\Definition(
     *         definition="Success",
     *         type="object",
     *         allOf={@SWG\Definition(ref="#/definitions/Context")},
     *         @SWG\Property(
     *             property="data",
     *             type="object",
     *             properties={@SWG\Property(property="message", type="string")}
     *         )
     *     ),
     *
     *     @SWG\Definition(
     *         definition="Error",
     *         type="object",
     *         allOf={@SWG\Definition(ref="#/definitions/Context")},
     *         @SWG\Property(
     *             property="errors",
     *             type="array",
     *             @SWG\Items(
     *                 @SWG\Property(property="message", type="string"),
     *                 @SWG\Property(property="code", type="integer"),
     *                 @SWG\Property(property="type", type="string"),
     *             )
     *         ),
     *         required={"errors"}
     *     ),
     *
     *     @SWG\Definition(
     *         definition="IdableEntity",
     *         @SWG\Property(property="id", type="integer"),
     *         required={"id"}
     *     ),
     *
     *     @SWG\Definition(
     *         definition="TransactionData",
     *         @SWG\Property(property="content", type="string"),
     *         required={"content"}
     *     ),
     *
     *     @SWG\Definition(
     *         definition="TransactionResponse",
     *         @SWG\Property(property="message", type="string"),
     *         required={"message"}
     *     ),
     *
     *     @SWG\Definition(
     *         definition="ReceiptResponse",
     *         @SWG\Property(property="reference_id", type="string"),
     *         @SWG\Property(property="origin", type="string"),
     *         @SWG\Property(property="status", type="string"),
     *         @SWG\Property(property="message", type="string"),
     *         required={"reference_id", "origin","status", "message"}
     *     ),
     *
     *     @SWG\Definition(
     *         definition="Transaction",
     *         allOf={
     *             @SWG\Definition(
     *                  @SWG\Property(property="referenceId", type="string"),
     *                  @SWG\Property(property="amount", type="integer"),
     *                  @SWG\Property(property="currency", type="string"),
     *                  @SWG\Property(property="createdAt", type="string", format="date-time"),
     *                  @SWG\Property(property="origin", type="string"),
     *                  @SWG\Property(property="vin", type="string"),
     *                  @SWG\Property(property="currentStatus", type="string")
     *             ),
     *         }
     *     ),
     *
     *     @SWG\Definition(
     *         definition="TransactionComplete",
     *         allOf={
     *             @SWG\Definition(ref="#/definitions/IdableEntity"),
     *             @SWG\Definition(ref="#/definitions/Transaction"),
     *         }
     *     ),
     *
     * )
     *
     * @SWG\SecurityScheme(
     *   securityDefinition="bearer",
     *   type="apiKey",
     *   in="header",
     *   name="Authorization"
     * )
     */
}
