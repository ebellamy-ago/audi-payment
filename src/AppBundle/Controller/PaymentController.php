<?php

namespace AppBundle\Controller;

use AppBundle\Entity\TransactionHistory;
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
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="reference_id",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="amount",
     *         type="integer",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="origin",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="lastname",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="phone",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="merchant_login",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="merchant_pwd",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="merchant_id",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="url_cancel",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="url_post_data",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="url_receipt",
     *         type="string",
     *         required=false,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="A JSON file with an element 'content'."
     *     )
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

        return new JsonResponse([
            'content' =>  $provider->retrievePaymentInformations($transaction)->getContent()
        ]);
    }

    /**
     * @Rest\Post("/transaction/{provider}", name="_payment")
     * @ParamConverter(name="transactionStatus", converter="transaction_converter")
     * @SWG\Post(
     *     path="/transaction/{provider}",
     *     description="Update a transaction by reference_id.",
     *     @SWG\Parameter(
     *         name="reference_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="result_label",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="transaction_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="auth_code",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="result_code",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="A JSON file with message result for payment."
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

        return new JsonResponse(['message' => $transactionStatus->getStatusMessage()]);
    }

    /**
     * @Rest\Post("/receipt/{provider}", name="_payment")
     * @ParamConverter(name="transactionStatus", converter="transaction_converter")
     * @SWG\Post(
     *     path="/receipt/{provider}",
     *     description="Url receipt.",
     *     @SWG\Parameter(
     *         name="reference_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="result_label",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="transaction_id",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="auth_code",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Parameter(
     *         name="result_code",
     *         type="string",
     *         required=true,
     *         in="query",
     *         description="Only for Magellan provider."
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="A JSON file with message and origin front."
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

        $response = [
            'reference_id' => $transaction->getReferenceId(),
            'status' => $transactionStatus->getStatus(),
            'origin' => $transaction->getOrigin()
        ];

        return new JsonResponse($response);
    }
}
