import * as React from "react";
import * as DialogPrimitive from "@radix-ui/react-dialog";

import { cn } from "@/lib/utils";

const Dialog = DialogPrimitive.Root;
const DialogTrigger = DialogPrimitive.Trigger;
const DialogPortal = DialogPrimitive.Portal;
const DialogClose = DialogPrimitive.Close;

const DialogOverlay = React.forwardRef(({ className, ...props }, ref) => <DialogPrimitive.Overlay ref={ref} className={cn("fixed inset-0 z-50 bg-slate-950/40 backdrop-blur-sm", className)} {...props} />);
DialogOverlay.displayName = DialogPrimitive.Overlay.displayName;

const DialogContent = React.forwardRef(({ className, children, ...props }, ref) => (
  <DialogPortal>
    <DialogOverlay />
    <DialogPrimitive.Content
      ref={ref}
      className={cn("fixed left-1/2 top-1/2 z-50 w-[calc(100%-2rem)] max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-lg border border-slate-200 bg-white p-6 shadow-sm outline-none", className)}
      {...props}
    >
      {children}
    </DialogPrimitive.Content>
  </DialogPortal>
));
DialogContent.displayName = DialogPrimitive.Content.displayName;

function DialogHeader({ className, ...props }) {
  return <div className={cn("flex flex-col gap-2", className)} {...props} />;
}

function DialogTitle({ className, ...props }) {
  return <DialogPrimitive.Title className={cn("text-xl font-semibold tracking-tight text-slate-900", className)} {...props} />;
}

function DialogDescription({ className, ...props }) {
  return <DialogPrimitive.Description className={cn("text-sm leading-7 text-slate-600", className)} {...props} />;
}

export { Dialog, DialogTrigger, DialogPortal, DialogOverlay, DialogClose, DialogContent, DialogHeader, DialogTitle, DialogDescription };
