<?php

namespace AppBundle\Controller;

use AppBundle\Entity\TransactionHistory;
use Mullenlowe\CommonBundle\Component\AMQP\CrudProducer;
use Mullenlowe\PayPluginBundle\Model\AbstractTransaction;
use Mullenlowe\PayPluginBundle\Model\MagellanStatusTransaction;
use Mullenlowe\PayPluginBundle\Model\StatusTransactionInterface;
use Mullenlowe\PayPluginBundle\Service\Provider\Providers;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as Rest;
use Mullenlowe\CommonBundle\Controller\MullenloweRestController;

/**
 * Class PaymentController
 * @package AppBundle\Controller
 */
class PaymentController extends MullenloweRestController
{
    const CONTEXT = 'Payment';

    /**
     * @Rest\Post("/", name="_payment")
     * @ParamConverter(name="transaction", converter="transaction_converter")
     * @SWG\Post(
     *     path="/",
     *     description="Retrieve payments informations like a payment page.",
     *     operationId="retrievePaymentsInformations",
     *     tags={"Payment"},
     *     @SWG\Parameter(
     *         name="provider",
     *         type="string",
     *         required=true,
     *         description="Name of the payment provider (ex: magellan).",
     *         in="query"
     *     ),
     *     @SWG\Parameter(
     *         name="currency",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Currency in which the transaction is established"
     *     ),
     *     @SWG\Parameter(
     *         name="reference_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Own reference transaction to the merchant"
     *     ),
     *     @SWG\Parameter(
     *         name="amount",
     *         type="integer",
     *         required=true,
     *         in="query",
     *         description="Transaction amount"
     *     ),
     *     @SWG\Parameter(
     *         name="origin",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Front origin"
     *     ),
     *     @SWG\Parameter(
     *         name="lastname",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="The last name of the buyer"
     *     ),
     *     @SWG\Parameter(
     *         name="phone",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="The buyer phone"
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="The name of the buyer"
     *     ),
     *     @SWG\Parameter(
     *         name="merchant_login",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Merchant login"
     *     ),
     *     @SWG\Parameter(
     *         name="merchant_pwd",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Merchant password"
     *     ),
     *     @SWG\Parameter(
     *         name="merchant_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Merchant id"
     *     ),
     *     @SWG\Parameter(
     *         name="url_cancel",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider, set to none"
     *     ),
     *     @SWG\Parameter(
     *         name="url_post_data",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider, set to none"
     *     ),
     *     @SWG\Parameter(
     *         name="url_receipt",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider, set to none"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="A JSON file with an element 'content'."
     *         @SWG\Schema(
     *             allOf={
     *                 @SWG\Definition(ref="#/definitions/Context"),
     *                 @SWG\Definition(
     *                     @SWG\Property(property="data", ref="#/definitions/TransactionData"),
     *                 )
     *             }
     *         )
     *     )
     *     @SWG\Response(
     *         response=500,
     *         description="retrieving data error",
     *         @SWG\Schema(ref="#/definitions/Error")
     *    )
     * )
     */
    public function getInformationsAction(AbstractTransaction $transaction, Providers $providers)
    {
        $manager = $this->getDoctrine()->getManager();

        $transactionHistory = new TransactionHistory($transaction->getReferenceId(), MagellanStatusTransaction::INITIALIZED);

        $manager->persist($transaction);
        $manager->persist($transactionHistory);
        $manager->flush();

        $provider = $providers->getByTransaction($transaction);

        return $this->createView(['content' =>  $provider->retrievePaymentInformations($transaction)->getContent()]);
    }

    /**
     * @Rest\Post("/transaction/{provider}", name="_payment")
     * @ParamConverter(name="transactionStatus", converter="transaction_converter")
     * @SWG\Post(
     *     path="/transaction/{provider}",
     *     description="Update a transaction by reference_id.",
     *     operationId="UpdateTransactionByReferenceId",
     *     tags={"Payment"},
     *     @SWG\Parameter(
     *         name="provider",
     *         type="string",
     *         required=true,
     *         in="path",
     *         description="Payment provider"
     *     ),
     *     @SWG\Parameter(
     *         name="reference_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, external reference number transmitted by the client"
     *     ),
     *     @SWG\Parameter(
     *         name="result_label",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, Explanatory text on the back"
     *     ),
     *     @SWG\Parameter(
     *         name="transaction_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, Tracking transaction ID"
     *     ),
     *     @SWG\Parameter(
     *         name="auth_code",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, authorization number of the transaction"
     *     ),
     *     @SWG\Parameter(
     *         name="result_code",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, return code"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="A JSON file with message result for payment."
     *         @SWG\Schema(
     *             allOf={
     *                 @SWG\Definition(ref="#/definitions/Context"),
     *                 @SWG\Definition(
     *                     @SWG\Property(property="data", ref="#/definitions/TransactionResponse"),
     *                 )
     *             }
     *         )
     *     )
     *     @SWG\Response(
     *         response=404,
     *         description="not transaction found",
     *         @SWG\Schema(ref="#/definitions/Error")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="updating transaction error",
     *         @SWG\Schema(ref="#/definitions/Error")
     *     )
     * )
     */
    public function transactionAction(StatusTransactionInterface $transactionStatus)
    {
        $referenceId = $transactionStatus->getReferenceId();
        $manager = $this->getDoctrine()->getManager();

        $hasTransaction = (bool) $manager
            ->getRepository(AbstractTransaction::class)
            ->findByReferenceId($referenceId)
        ;

        if (false === $hasTransaction) {
            throw $this->createNotFoundException(
                sprintf('No transaction found with the reference_id "%s"', $referenceId)
            );
        }

        $manager->persist(new TransactionHistory($referenceId, $transactionStatus->getStatus()));
        $manager->flush();

        return $this->createView(['message' => $transactionStatus->getStatusMessage()]);
    }

    /**
     * @Rest\Post("/receipt/{provider}", name="_payment")
     * @ParamConverter(name="transactionStatus", converter="transaction_converter")
     * @SWG\Post(
     *     path="/receipt/{provider}",
     *     description="Url receipt.",
     *     operationId="receiptTransactionUrl",
     *     tags={"Payment"},
     *     @SWG\Parameter(
     *         name="provider",
     *         type="string",
     *         required=true,
     *         in="path",
     *         description="Payment provider"
     *     ),
     *     @SWG\Parameter(
     *         name="reference_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, external reference number transmitted by the client"
     *     ),
     *     @SWG\Parameter(
     *         name="result_label",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, Explanatory text on the back"
     *     ),
     *     @SWG\Parameter(
     *         name="transaction_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, Tracking transaction ID"
     *     ),
     *     @SWG\Parameter(
     *         name="auth_code",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, authorization number of the transaction"
     *     ),
     *     @SWG\Parameter(
     *         name="result_code",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Magellan provider, return code"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="A JSON file with message and origin front."
     *         @SWG\Schema(
     *             allOf={
     *                 @SWG\Definition(ref="#/definitions/Context"),
     *                 @SWG\Definition(
     *                     @SWG\Property(property="data", ref="#/definitions/ReceiptResponse"),
     *                 )
     *             }
     *         )
     *     )
     *     @SWG\Response(
     *         response=404,
     *         description="not found",
     *         @SWG\Schema(ref="#/definitions/Error")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="updating transaction error",
     *         @SWG\Schema(ref="#/definitions/Error")
     *     )
     * )
     */
    public function receiptAction(StatusTransactionInterface $transactionStatus)
    {
        $referenceId = $transactionStatus->getReferenceId();
        $manager = $this->getDoctrine()->getManager();

        /** @var AbstractTransaction $transaction */
        $transaction = $manager->getRepository(AbstractTransaction::class)->findOneByReferenceId($referenceId);

        if (!$transaction) {
            throw $this->createNotFoundException(
                sprintf('No transaction found with the reference_id "%s"', $referenceId)
            );
        }

        /** @var CrudProducer $paymentProducer */
        $paymentProducer = $this->get('old_sound_rabbit_mq.payment_crud_producer');
        $paymentProducer->publishJson(['reference_id' => $transaction->getReferenceId()], self::CONTEXT, 'update');

        $response = [
            'reference_id' => $transaction->getReferenceId(),
            'origin' => $transaction->getOrigin(),
            'status' => $transactionStatus->getStatus(),
            'message' => $transactionStatus->getStatusMessage(),
        ];

        return $this->createView($response);
    }
}
